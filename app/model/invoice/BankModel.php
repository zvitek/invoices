<?php
namespace App\Model\Invoice;

use App\Helper\Data;
use App\Helper\Database;
use Dibi\Connection;
use Dibi\DateTime;
use Dibi\Exception;
use Joseki\Application\Responses\PdfResponse;
use Latte\Engine;
use Nette\Security\User;
use Nette\Utils\Random;
use Tracy\Debugger;

class BankModel extends Config
{
	/** @var Connection */
	private $db;

	/** @var User */
	private $user;

	public function __construct(Connection $connection, User $user) {
		$this->db = $connection;
		$this->user = $user;
	}

	/************************************************************************************************************z*v***/
	/********** STRUCTURE **********/

	public function account__data($banks) {
		$buffer = [];
		$selection = Database::selection($banks);
		if(is_null($selection)) {
			return Data::return__empty($banks);
		}
		$bank_Data = $this->db->select('*')->from(self::TABLE_BANK_ACCOUNTS)->where($selection)->fetchAll();
		if(count($bank_Data)) {
			foreach($bank_Data as $bank) {
				$structure = [
					'id' => $bank[self::COLUMN_ID],
					'name' => $bank[self::COLUMN_NAME],
					'account' => [
						'number' => $bank[self::COLUMN_NUMBER],
						'code' => $bank[self::COLUMN_CODE],
					],
				];
				if(is_numeric($banks)) {
					return $structure;
				}
				else {
					$buffer[] = $structure;
				}
			}
		}
		if(!count($buffer)) {
			return Data::return__empty($banks);
		}
		return $buffer;
	}

	/************************************************************************************************************z*v***/
	/********** GETTER **********/

	public function accounts__ids() {
		$account_Data = $this->db->select(self::COLUMN_ID)->from(self::TABLE_BANK_ACCOUNTS)->fetchAll();
		return count($account_Data) ? Data::prepare_ids($account_Data) : [];
	}

	public function accounts__select() {
		$buffer = [];
		$accounts = $this->account__data($this->accounts__ids());
		if(count($accounts)) {
			foreach($accounts as $account) {
				$buffer[$account['id']] = $account['name'];
			}
		}
		return $buffer;
	}

	/************************************************************************************************************z*v***/
	/********** CONTROL **********/
	
	public function account__create($name) {
		$data = [
			self::COLUMN_NAME => $name,
			self::COLUMN_CREATED => new DateTime(),
		];
		try {
			return $this->db->insert(self::TABLE_BANK_ACCOUNTS, $data)->execute(\dibi::IDENTIFIER);
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}
}