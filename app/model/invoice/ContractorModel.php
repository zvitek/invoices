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

class ContractorModel extends Config
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

	public function contractor__data($contractors) {
		$buffer = [];
		$selection = Database::selection($contractors);
		if(is_null($selection)) {
			return Data::return__empty($contractors);
		}
		$contractor_Data = $this->db->select('*')->from(self::TABLE_CONTRACTORS)->where($selection)->fetchAll();
		if(count($contractor_Data)) {
			foreach($contractor_Data as $contractor) {
				$structure = [
					'id' => $contractor[self::COLUMN_ID],
					'name' => $contractor[self::COLUMN_NAME],
					'address' => [
						'street' => $contractor[self::COLUMN_STREET],
						'city' => $contractor[self::COLUMN_CITY],
						'zip' => $contractor[self::COLUMN_ZIP],
					],
					'company' => [
						'ic' => $contractor[self::COLUMN_IC],
						'dic' => $contractor[self::COLUMN_DIC],
					],
					'contact' => [
						'phone' => $contractor[self::COLUMN_PHONE],
						'email' => $contractor[self::COLUMN_EMAIL],
					],
					'info' => [
						'payer_vat' => $contractor[self::COLUMN_PAYER_VAT],
					],
				];
				if(is_numeric($contractors)) {
					return $structure;
				}
				else {
					$buffer[] = $structure;
				}
			}
		}
		if(!count($buffer)) {
			return Data::return__empty($contractors);
		}
		return $buffer;
	}

	/************************************************************************************************************z*v***/
	/********** GETTER **********/

	public function contractors__ids() {
		$contractor_Data = $this->db->select(self::COLUMN_ID)->from(self::TABLE_CONTRACTORS)->fetchAll();
		return count($contractor_Data) ? Data::prepare_ids($contractor_Data) : [];
	}
	
	public function contractors__select() {
		$buffer = [];
		$contractors = $this->contractor__data($this->contractors__ids());
		if(count($contractors)) {
			foreach($contractors as $contractor) {
				$buffer[$contractor['id']] = $contractor['name'];
			}
		}
		return $buffer;
	}

	/************************************************************************************************************z*v***/
	/********** CONTROL **********/
	
	public function contractor__create($name) {
		$data = [
			self::COLUMN_NAME => $name,
			self::COLUMN_CREATED => new DateTime(),
		];
		try {
			return $this->db->insert(self::TABLE_CONTRACTORS, $data)->execute(\dibi::IDENTIFIER);
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}
}