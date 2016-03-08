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

class ClientModel extends Config
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

	public function client__data($clients) {
		$buffer = [];
		$selection = Database::selection($clients);
		if(is_null($selection)) {
			return Data::return__empty($clients);
		}
		$client_Data = $this->db->select('*')->from(self::TABLE_CLIENTS)->where($selection)->fetchAll();
		if(count($client_Data)) {
			foreach($client_Data as $client) {
				$structure = [
					'id' => $client[self::COLUMN_ID],
					'name' => $client[self::COLUMN_NAME],
					'address' => [
						'street' => $client[self::COLUMN_STREET],
						'city' => $client[self::COLUMN_CITY],
						'zip' => $client[self::COLUMN_ZIP],
					],
					'company' => [
						'ic' => $client[self::COLUMN_IC],
						'dic' => $client[self::COLUMN_DIC],
					]
				];
				if(is_numeric($clients)) {
					return $structure;
				}
				else {
					$buffer[] = $structure;
				}
			}
		}
		if(!count($buffer)) {
			return Data::return__empty($clients);
		}
		return $buffer;
	}

	/************************************************************************************************************z*v***/
	/********** GETTER **********/

	public function clients__ids() {
		$client_Data = $this->db->select(self::COLUMN_ID)->from(self::TABLE_CLIENTS)->fetchAll();
		return count($client_Data) ? Data::prepare_ids($client_Data) : [];
	}
	
	public function clients__select() {
		$buffer = [];
		$clients = $this->client__data($this->clients__ids());
		if(count($clients)) {
			foreach($clients as $client) {
				$buffer[$client['id']] = $client['name'];
			}
		}
		return $buffer;
	}

	/************************************************************************************************************z*v***/
	/********** CONTROL **********/
	
	public function client__create($name) {
		$data = [
			self::COLUMN_NAME => $name,
			self::COLUMN_CREATED => new DateTime(),
		];
		try {
			return $this->db->insert(self::TABLE_CLIENTS, $data)->execute(\dibi::IDENTIFIER);
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}
}