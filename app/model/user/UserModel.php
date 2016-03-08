<?php
namespace App\Model\User;

use App\Helper;
use Dibi\Connection;
use Dibi\Exception;
use Nette\Http\Session;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use Tracy\Debugger;

class UserModel extends Config
{
	/** @var Connection */
	private $db;

	/** @var Session */
	private $session;

	public function __construct(Connection $connection, Session $session) {
		$this->db = $connection;
		$this->session = $session;
	}

	/**
	 * @param array|int $user
	 * @param array $data
	 * @param bool|FALSE $by_Key
	 * @return array|null
	 */
	public function user__data($user, $data, $by_Key = FALSE) {
		$buffer = [];
		$selection = Helper\Database::selection($user);
		$user_Data = $this->db->select('*')->from(self::TABLE_USERS);
		if(!is_null($selection)) {
			$user_Data->where($selection);
		}
		$user_Data = $user_Data->fetchAll();
		if(count($user_Data)) {
			foreach($user_Data as $item) {
				$structure = [];
				$structure['id'] = $item[self::COLUMN_ID];
				$structure['token'] = $item[self::COLUMN_TOKEN];
				if(in_array(self::STRUCTURE_NAME, $data) || !count($data)) {
					$structure['name'] = [
						'first' => $item[self::COLUMN_NAME],
						'last' => $item[self::COLUMN_SURNAME],
						'full' => is_null($item[self::COLUMN_NAME]) && is_null($item[self::COLUMN_SURNAME])
							? NULL : join(' ', [$item[self::COLUMN_NAME], $item[self::COLUMN_SURNAME]]),
						'full_revert' => is_null($item[self::COLUMN_NAME]) && is_null($item[self::COLUMN_SURNAME])
							? NULL : join(' ', [$item[self::COLUMN_SURNAME], $item[self::COLUMN_NAME]]),
						'initials' => is_null($item[self::COLUMN_NAME]) && is_null($item[self::COLUMN_SURNAME])
							? NULL : substr($item[self::COLUMN_NAME], 0, 1) . substr($item[self::COLUMN_SURNAME], 0, 1),
					];
				}
				if(in_array(self::STRUCTURE_CONTACT, $data) || !count($data)) {
					$structure['contact'] = [
						'email' => $item[self::COLUMN_EMAIL],
					];
				}
				if(in_array(self::STRUCTURE_PASSWORD, $data)) {
					$structure['password'] = $item[self::COLUMN_PASSWORD];
				}
				if(in_array(self::STRUCTURE_REGISTRATION, $data) || !count($data)) {
					$structure['registration'] = [
						'activation' => $item[self::COLUMN_ACTIVE],
						'created' => $item[self::COLUMN_CREATED],
					];
				}
				if(is_numeric($user)) {
					return $structure;
				}
				if($by_Key) {
					$buffer[$item[self::COLUMN_ID]] = $structure;
				}
				else {
					$buffer[] = $structure;
				}
			}
		}
		if(!count($buffer)) {
			return Helper\Data::return__empty($user);
		}
		return $buffer;
	}

	/**
	 * @param int|array $roles
	 * @return array|null
	 */
	public function roles__data($roles) {
		$buffer = [];
		$role_Data = $this->db->select('*')->from(self::TABLE_USER_ROLES);
		$selection = Helper\Database::selection($roles);
		if(!is_null($selection)) {
			$role_Data->where($selection);
		}
		$role_Data->fetchAll();
		if(count($role_Data)) {
			foreach($role_Data as $role) {
				$structure = [
					'id' => $role[self::COLUMN_ID],
					'name' => [
						'key' => $role[self::COLUMN_KEY_NAME],
						'role' => $role[self::COLUMN_NAME],
					],
				];
				if(is_numeric($roles)) {
					return $structure;
				}
				else {
					$buffer[] = $structure;
				}
			}
		}
		if(!count($buffer)) {
			return Helper\Data::return__empty($roles);
		}
		return $buffer;
	}

	/**************************************************************************************************************z*v*/
	/******************* CONTROL *******************/

	/**
	 * @param $email
	 * @return bool:FALSE|int
	 */
	public function user__registration($email) {
		$data = [
			self::COLUMN_EMAIL => Strings::lower($email),
			self::COLUMN_CREATED => new DateTime(),
			self::COLUMN_TOKEN => sha1(Strings::lower($email)),
		];
		try {
			$user_ID = $this->db->insert(self::TABLE_USERS, $data)->execute(\dibi::IDENTIFIER);
			$this->userRole__add($user_ID, [self::DEFAULT_ROLE]);
			return $this->user__data($user_ID, [NULL]);
		}
		catch(Exception $e) {
			Debugger::log($e);
			return NULL;
		}
	}

	public function user__update($user_ID, $data) {
		$clear_Data = [];
		if(is_null($user_ID)) {
			return FALSE;
		}
		if(isset($data[self::COLUMN_ACTIVE])) {
			if($data[self::COLUMN_ACTIVE] instanceof DateTime) {
				$clear_Data[self::COLUMN_ACTIVE] = $data[self::COLUMN_ACTIVE];
			}
		}
		if(array_key_exists(self::COLUMN_PASSWORD_TOKEN, $data) && array_key_exists(self::COLUMN_PASSWORD_EXPIRATION, $data)) {
			if(($data[self::COLUMN_PASSWORD_EXPIRATION] instanceof DateTime || is_null($data[self::COLUMN_PASSWORD_EXPIRATION])) &&
				(is_null($data[self::COLUMN_PASSWORD_TOKEN]) || strlen($data[self::COLUMN_PASSWORD_TOKEN]) == 40)) {
				$clear_Data[self::COLUMN_PASSWORD_TOKEN] = $data[self::COLUMN_PASSWORD_TOKEN];
				$clear_Data[self::COLUMN_PASSWORD_EXPIRATION] = $data[self::COLUMN_PASSWORD_EXPIRATION];
			}
		}
		if(array_key_exists(self::COLUMN_PASSWORD, $data)) {
			$clear_Data[self::COLUMN_PASSWORD] = Passwords::hash($data[self::COLUMN_PASSWORD]);
		}
		if(array_key_exists(self::COLUMN_NAME, $data)) {
			$clear_Data[self::COLUMN_NAME] = Helper\Data::pick($data[self::COLUMN_NAME]);
		}
		if(array_key_exists(self::COLUMN_SURNAME, $data)) {
			$clear_Data[self::COLUMN_SURNAME] = Helper\Data::pick($data[self::COLUMN_SURNAME]);
		}
		if(array_key_exists(self::COLUMN_EMAIL, $data)) {
			$clear_Data[self::COLUMN_EMAIL] = Validators::isEmail($data[self::COLUMN_EMAIL])
				? $data[self::COLUMN_EMAIL] : NULL;
		}
		if(array_key_exists(self::COLUMN_ROLES, $data)) {
			$this->userRole__remove($user_ID);
			$this->userRole__add($user_ID, $data[self::COLUMN_ROLES]);
		}
		try {
			$this->db->update(self::TABLE_USERS, $clear_Data)->where(self::COLUMN_ID, '= %i', $user_ID)->execute();
			return TRUE;
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}

	/**
	 * @param int $user_ID
	 * @return bool
	 */
	public function userRole__remove($user_ID) {
		try {
			$this->db->delete(self::TABLE_USER_HAS_ROLE)->where(self::COLUMN_USERS_ID, '= %i', $user_ID)->execute();
			return TRUE;
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}

	/**
	 * @param int $user_ID
	 * @param array $roles
	 * @return bool
	 */
	public function userRole__add($user_ID, $roles) {
		try {
			foreach($roles as $role) {
				$this->db->insert(self::TABLE_USER_HAS_ROLE, [
					self::COLUMN_USERS_ID => $user_ID,
					self::COLUMN_ROLES_ID => $role]
				)->execute();
			}
			return TRUE;
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}

	/**************************************************************************************************************z*v*/
	/******************* SET *******************/

	/**
	 * @param $token
	 * @return bool|null
	 */
	public function set__active($token) {
		$user = $this->user__byToken($token);
		if(!is_null($user)) {
			if(is_null($user['registration']['activation'])) {
				return $this->user__update($user[self::COLUMN_ID], [self::COLUMN_ACTIVE => new DateTime()]);
			}
			return NULL;
		}
		return FALSE;
	}

	public function set__passwordToken($user_ID) {
		$dateTime = new DateTime();
		$token = sha1(Random::generate(15));
		if($this->user__update($user_ID, [
			self::COLUMN_PASSWORD_TOKEN => $token,
			self::COLUMN_PASSWORD_EXPIRATION => $dateTime->modify('+1 day'),
		])) {
			return $token;
		} else {
			return NULL;
		}
	}

	public function set__password($user_ID, $password) {
		return $this->user__update($user_ID, [
			self::COLUMN_PASSWORD => $password,
			self::COLUMN_PASSWORD_EXPIRATION => NULL,
			self::COLUMN_PASSWORD_TOKEN => NULL,
		]);
	}

	/**************************************************************************************************************z*v*/
	/******************* HELPERS *******************/

	/**
	 * @param $email string
	 * @param $user_ID int|null
	 * @return bool
	 */
	public function email__isRegistered($email, $user_ID = NULL) {
		return $this->db->select(self::COLUMN_ID)->from(self::TABLE_USERS)
			->where(self::COLUMN_EMAIL, '= %s', Strings::lower($email))
			->where(self::COLUMN_ID, '%if', is_null($user_ID), 'IS NOT NULL', '%else', '!= %i', $user_ID, '%end')->fetch() ? TRUE : FALSE;
	}

	/**
	 * @param $user_Token string
	 * @return array|null
	 */
	public function user__byToken($user_Token) {
		$get_User =  $this->db->select(self::COLUMN_ID)->from(self::TABLE_USERS)->where(self::COLUMN_TOKEN, '= %s', $user_Token)->fetch();
		return $get_User ? $this->user__data($get_User[self::COLUMN_ID], []) : NULL;
	}

	/**
	 * @param $email string
	 * @return array|null
	 */
	public function user__byEmail($email) {
		$get_User =  $this->db->select(self::COLUMN_ID)->from(self::TABLE_USERS)->where(self::COLUMN_EMAIL, '= %s', Strings::lower($email))->fetch();
		return $get_User ? $this->user__data($get_User[self::COLUMN_ID], []) : NULL;
	}

	/**
	 * @param string $password_Token
	 * @return null|void
	 */
	public function password__byToken($password_Token) {
		$get_User = $this->db->select([self::COLUMN_ID, self::COLUMN_PASSWORD_TOKEN])->from(self::TABLE_USERS)
			->where(self::COLUMN_PASSWORD_TOKEN, '= %s', $password_Token)
			->where(self::COLUMN_PASSWORD_EXPIRATION, '>= %t', new DateTime())->fetch();
		return $get_User ? $get_User[self::COLUMN_ID] : NULL;
	}

	/**
	 * @param int $user_ID
	 * @param array $roles
	 * @return bool
	 */
	public function user__hasRoles($user_ID, $roles) {
		$user_Roles = $this->userRoles__keyName($user_ID);
		if(!count($user_Roles)) {
			return FALSE;
		}
		$role_Counter = 0;
		foreach($user_Roles as $role) {
			if(in_array($role, $roles)) {
				$role_Counter++;
			}
		}
		return $role_Counter == count($roles);
	}

	/**
	 * @param int $user_ID
	 * @return bool
	 */
	public function user__remove($user_ID) {
		try {
			$this->db->delete(self::TABLE_USERS)->where(self::COLUMN_ID, '= %i', $user_ID)->execute();
			return TRUE;
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}

	/**************************************************************************************************************z*v*/
	/******************* GETTER *******************/

	/**
	 * @param int $user_ID
	 * @return array
	 */
	public function userRoles__ids($user_ID) {
		$userRoles_Data = $this->db->select(self::COLUMN_ROLES_ID)->from(self::TABLE_USER_HAS_ROLE)->where(self::COLUMN_USERS_ID, '= %i', $user_ID)->fetchAll();
		$userRoles_ids = Helper\Data::prepare_ids($userRoles_Data, self::COLUMN_ROLES_ID);
		return $userRoles_ids;
	}

	/**
	 * @param int $user_ID
	 * @return array
	 */
	public function userRoles__keyName($user_ID) {
		$buffer = [];
		$roles_ids = $this->userRoles__ids($user_ID);
		if(!count($roles_ids)) {
			return [];
		}
		$roles_Data = $this->roles__data($roles_ids);
		if(count($roles_Data)) {
			foreach($roles_Data as $role) {
				$buffer[] = $role['name']['key'];
			}
		}
		return $buffer;
	}

	/**
	 * @param bool|TRUE $active
	 * @param null|array $roles
	 * @return array
	 */
	public function usersList__ids($active = TRUE, $roles = NULL) {
		$users = $this->db->select(self::COLUMN_ID)->from(self::TABLE_USERS)->where(self::COLUMN_ACTIVE, '%if', $active, 'IS NOT NULL', '%else', 'IS NULL', '%end');
		if(is_array($roles)) {
			$roles = array_filter($roles);
			if(count($roles)) {
				$roles = $this->db->select(self::COLUMN_USERS_ID)->from(self::TABLE_USER_HAS_ROLE)->where(self::COLUMN_ROLES_ID, 'IN %in', $roles)->groupBy(self::COLUMN_USERS_ID)->fetchAll();
				$roles_ids = Helper\Data::prepare_ids($roles, self::COLUMN_USERS_ID);
				if(!count($roles_ids)) {
					return [];
				}
				$users->where(self::COLUMN_ID, 'IN %in', $roles_ids);
			}
		}
		$users->fetchAll();
		return count($users) ? Helper\Data::prepare_ids($users) : [];
	}

	/**************************************************************************************************************z*v*/
	/******************* SESSION *******************/
	
	public function getSection() {
		return $this->session->getSection('user.Section');
	}
}