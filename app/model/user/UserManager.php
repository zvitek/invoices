<?php

namespace App\Model\User;

use Nette;
use Nette\Security\Passwords;

class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{
	const
		TABLE_NAME = 'users',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'username',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role';


	/** @var UserModel */
	private $userModel;

	public function __construct(UserModel $userModel) {
		$this->userModel = $userModel;
	}

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials) {
		list($email, $password) = $credentials;
		$get_User = $this->userModel->user__byEmail($email);

		if(is_null($get_User)) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		}
		$user = $this->userModel->user__data($get_User[Config::COLUMN_ID], [Config::STRUCTURE_PASSWORD]);

		if(!Passwords::verify($password, $user[Config::COLUMN_PASSWORD])) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		} elseif (Passwords::needsRehash($user[Config::COLUMN_PASSWORD])) {
			$this->userModel->user__update($user[Config::COLUMN_ID], [
				Config::COLUMN_PASSWORD => Passwords::hash($password),
			]);
		}
		$user = $this->userModel->user__data($get_User[Config::COLUMN_ID], []);
		return new Nette\Security\Identity($user[Config::COLUMN_ID], $this->userModel->userRoles__keyName($get_User[Config::COLUMN_ID]), $user);
	}
}