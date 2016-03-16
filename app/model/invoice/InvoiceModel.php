<?php
namespace App\Model\Invoice;

use App\Helper\Data;
use App\Helper\Database;
use Dibi\Connection;
use Dibi\DateTime;
use Dibi\Exception;
use Joseki\Application\Responses\PdfResponse;
use Latte\Engine;
use Nette\DI\Container;
use Nette\Security\User;
use Nette\Utils\Random;
use Nette\Utils\Validators;
use Tracy\Debugger;

class InvoiceModel extends Config
{
	/** @var Connection */
	private $db;

	/** @var BankModel */
	private $bankModel;

	/** @var ClientModel */
	private $clientModel;

	/** @var ContractorModel */
	private $contractorModel;

	/** @var User */
	private $user;

	/** @var array */
	private $config;

	public function __construct(Connection $connection, User $user, BankModel $bankModel, ClientModel $clientModel, ContractorModel $contractorModel, Container $container) {
		$this->db = $connection;
		$this->bankModel = $bankModel;
		$this->clientModel = $clientModel;
		$this->contractorModel = $contractorModel;
		$this->user = $user;
		$this->config = $container->getParameters();
	}

	/************************************************************************************************************z*v***/
	/********** STRUCTURE **********/

	public function invoice__data($invoices, $data = []) {
		$buffer = [];
		$selection = Database::selection($invoices);
		if(is_null($selection)) {
			return Data::return__empty($invoices);
		}
		$invoice_Data = $this->db->select('*')->from(self::TABLE_INVOICES)->where($selection)->fetchAll();
		if(count($invoice_Data)) {
			foreach($invoice_Data as $invoice) {
				$structure = [
					'id' => $invoice[self::COLUMN_ID],
					'token' => $invoice[self::COLUMN_TOKEN],
					'number' => $invoice[self::COLUMN_NUMBER],
				];
				if(in_array(self::STRUCTURE_DATE, $data) || !count($data)) {
					$structure['date'] = [
						'issue' => $invoice[self::COLUMN_ISSUE_DATE],
						'due' => $invoice[self::COLUMN_DATE_DUE],
						'created' => $invoice[self::COLUMN_CREATED],
					];
				}
				if(in_array(self::STRUCTURE_PRICE, $data) || !count($data)) {
					$structure['price'] = [
						'without_vat' => $invoice[self::COLUMN_PRICE],
						'with_vat' => $invoice[self::COLUMN_PRICE_VAT],
					];
				}
				if(in_array(self::STRUCTURE_PARAMETERS, $data) || !count($data)) {
					$structure['param'] = [
						'paid' => $invoice[self::COLUMN_PAID],
						'pricing' => $invoice[self::COLUMN_PRICING],
						'sent' => $invoice[self::COLUMN_SENT],
					];
				}
				if(in_array(self::STRUCTURE_BANKS, $data) || !count($data)) {
					$structure['bank'] = $this->bankModel->account__data($invoice[self::COLUMN_BANK_ACCOUNTS_ID]);
				}
				if(in_array(self::STRUCTURE_CLIENTS, $data) || !count($data)) {
					$structure['client'] = $this->clientModel->client__data($invoice[self::COLUMN_CLIENTS_ID]);
				}
				if(in_array(self::STRUCTURE_CONTRACTORS, $data) || !count($data)) {
					$structure['contractor'] = $this->contractorModel->contractor__data($invoice[self::COLUMN_CONTRACTORS_ID]);
				}
				if(in_array(self::STRUCTURE_ITEMS, $data) || !count($data)) {
					$structure['items'] = $this->invoiceItem__data($this->invoiceItems_byInvoice__ids($invoice[self::COLUMN_ID]));
				}
				if(is_numeric($invoices)) {
					return $structure;
				}
				else {
					$buffer[] = $structure;
				}
			}
		}
		if(!count($buffer)) {
			return Data::return__empty($invoices);
		}
		return $buffer;
	}

	public function invoiceItem__data($items) {
		$buffer = [];
		$selection = Database::selection($items);
		if(is_null($selection)) {
			return Data::return__empty($items);
		}
		$invoiceItems_Data = $this->db->select('*')->from(self::TABLE_INVOICE_ITEMS)->where($selection)->fetchAll();
		if(count($invoiceItems_Data)) {
			foreach($invoiceItems_Data as $item) {
				$structure = [
					'id' => $item[self::COLUMN_ID],
					'invoices_id' => $item[self::COLUMN_INVOICES_ID],
					'basic' => [
						'name' => $item[self::COLUMN_NAME],
						'description' => $item[self::COLUMN_DESCRIPTION],
					],
					'price' => [
						'per_unit' => $item[self::COLUMN_PRICE_PER_UNIT],
						'units' => $item[self::COLUMN_UNITS],
						'total' => $item[self::COLUMN_TOTAL],
					],
					'date' => [
						'created' => $item[self::COLUMN_CREATED],
					],
				];
				if(is_numeric($items)) {
					return $structure;
				}
				$buffer[] = $structure;
			}
		}
		if(!count($buffer)) {
			return Data::return__empty($items);
		}
		return $buffer;
	}

	/************************************************************************************************************z*v***/
	/********** GETTER **********/
	
	public function invoiceFilter__ids() {
		$invoice_Data = $this->db->select(self::COLUMN_ID)->from(self::TABLE_INVOICES)->fetchAll();
		return count($invoice_Data) ? Data::prepare_ids($invoice_Data) : [];
	}

	public function invoiceItems_byInvoice__ids($invoice_ID) {
		$items_Data = $this->db->select(self::COLUMN_ID)->from(self::TABLE_INVOICE_ITEMS)->where(self::COLUMN_INVOICES_ID, '= %i', $invoice_ID)->fetchAll();
		return count($items_Data) ? Data::prepare_ids($items_Data) : [];
	}

	/************************************************************************************************************z*v***/
	/********** HELPER **********/

	public function invoice_byToken__id($invoice_Token) {
		$invoice_Data = $this->db->select(self::COLUMN_ID)->from(self::TABLE_INVOICES)->where(self::COLUMN_TOKEN, '= %s', $invoice_Token)->fetch();
		return $invoice_Data ? $invoice_Data[self::COLUMN_ID] : NULL;
	}

	public function accounts__select() {
		return $this->bankModel->accounts__select();
	}

	public function contractors__select() {
		return $this->contractorModel->contractors__select();
	}

	public function clients__select() {
		return $this->clientModel->clients__select();
	}

	/************************************************************************************************************z*v***/
	/********** CONTROL **********/
	
	public function invoice__create() {
		$count_invoices = $this->db->select(self::COLUMN_ID)->from(self::TABLE_INVOICES)
			->where('(YEAR(issue_date) = %i AND MONTH(issue_date) = %i)', date('Y'), date('m'))->count();
		$count_invoices = $count_invoices + 1;
		$count_invoices = $count_invoices < 10 ? '0' . $count_invoices : $count_invoices;
		$data = [
			self::COLUMN_NUMBER => sprintf('%s%s%s', date('Y'), date('m'), ($count_invoices + 1)),
			self::COLUMN_CREATED => new DateTime(),
			self::COLUMN_ISSUE_DATE => new DateTime(),
			self::COLUMN_TOKEN => Random::generate(40),
			self::COLUMN_USERS_ID => $this->user->id,
		];
		try {
			$this->db->insert(self::TABLE_INVOICES, $data)->execute();
			return $data[self::COLUMN_TOKEN];
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}

	public function invoiceItem__create($invoice_ID) {
		try {
			return $this->db->insert(self::TABLE_INVOICE_ITEMS, [
				self::COLUMN_INVOICES_ID => $invoice_ID,
				self::COLUMN_CREATED  => new DateTime(),
			])->execute(\dibi::IDENTIFIER);
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}

	public function invoiceItem__remove($item_ID) {
		try {
			$this->db->delete(self::TABLE_INVOICE_ITEMS)->where(self::COLUMN_ID, '= %i', $item_ID)->execute();
			return TRUE;
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}

	public function invoice__update($invoice_ID, $data) {
		$update = [];
		if(!is_numeric($invoice_ID)) {
			return FALSE;
		}
		if(array_key_exists(self::COLUMN_NUMBER, $data)) {
			$update[self::COLUMN_NUMBER] = $data[self::COLUMN_NUMBER];
		}
		if(array_key_exists(self::COLUMN_DATE_DUE, $data)) {
			if($data[self::COLUMN_DATE_DUE] instanceof \Nette\Utils\DateTime) {
				$update[self::COLUMN_DATE_DUE] = $data[self::COLUMN_DATE_DUE];
			}
		}
		if(array_key_exists(self::COLUMN_ISSUE_DATE, $data)) {
			if($data[self::COLUMN_ISSUE_DATE] instanceof \Nette\Utils\DateTime) {
				$update[self::COLUMN_ISSUE_DATE] = $data[self::COLUMN_ISSUE_DATE];
			}
		}
		if(array_key_exists(self::COLUMN_PRICE, $data)) {
			$update[self::COLUMN_PRICE] = is_numeric($data[self::COLUMN_PRICE])
				? $data[self::COLUMN_PRICE] : NULL;
		}
		if(array_key_exists(self::COLUMN_PRICE_VAT, $data)) {
			$update[self::COLUMN_PRICE_VAT] = is_numeric($data[self::COLUMN_PRICE_VAT])
				? $data[self::COLUMN_PRICE_VAT] : NULL;
		}
		if(array_key_exists(self::COLUMN_PAID, $data)) {
			$update[self::COLUMN_PAID] = $data[self::COLUMN_PAID] ? TRUE : FALSE;
		}
		if(array_key_exists(self::COLUMN_PRICING, $data)) {
			$update[self::COLUMN_PRICING] = $data[self::COLUMN_PRICING] ? TRUE : FALSE;
		}
		if(array_key_exists(self::COLUMN_BANK_ACCOUNTS_ID, $data)) {
			if(array_key_exists($data[self::COLUMN_BANK_ACCOUNTS_ID], $this->bankModel->accounts__select())) {
				$update[self::COLUMN_BANK_ACCOUNTS_ID] = $data[self::COLUMN_BANK_ACCOUNTS_ID];
			}
		}
		if(array_key_exists(self::COLUMN_CONTRACTORS_ID, $data)) {
			if(array_key_exists($data[self::COLUMN_CONTRACTORS_ID], $this->contractorModel->contractors__select())) {
				$update[self::COLUMN_CONTRACTORS_ID] = $data[self::COLUMN_CONTRACTORS_ID];
			}
		}
		if(array_key_exists(self::COLUMN_CLIENTS_ID, $data)) {
			if(array_key_exists($data[self::COLUMN_CLIENTS_ID], $this->clientModel->clients__select())) {
				$update[self::COLUMN_CLIENTS_ID] = $data[self::COLUMN_CLIENTS_ID];
			}
		}
		if(count($update)) {
			try {
				$this->db->update(self::TABLE_INVOICES, $update)->where(self::COLUMN_ID, '= %i', $invoice_ID)->execute();
				return TRUE;
			}
			catch(Exception $e) {
				Debugger::log($e);
				return FALSE;
			}
		}
		return FALSE;
	}

	public function invoiceItem__update($item_ID, $data) {
		$update = [];
		if(!is_numeric($item_ID)) {
			return FALSE;
		}
		if(array_key_exists(self::COLUMN_NAME, $data)) {
			$update[self::COLUMN_NAME] = Data::pick($data[self::COLUMN_NAME]);
		}
		if(array_key_exists(self::COLUMN_DESCRIPTION, $data)) {
			$update[self::COLUMN_DESCRIPTION] = Data::pick($data[self::COLUMN_DESCRIPTION]);
		}
		if(array_key_exists(self::COLUMN_PRICE_PER_UNIT, $data)) {
			if(is_numeric($data[self::COLUMN_PRICE_PER_UNIT])) {
				$update[self::COLUMN_PRICE_PER_UNIT] = $data[self::COLUMN_PRICE_PER_UNIT];
			}
		}
		if(array_key_exists(self::COLUMN_TOTAL, $data)) {
			if(is_numeric($data[self::COLUMN_TOTAL])) {
				$update[self::COLUMN_TOTAL] = $data[self::COLUMN_TOTAL];
			}
		}
		if(array_key_exists(self::COLUMN_UNITS, $data)) {
			if(is_numeric($data[self::COLUMN_UNITS])) {
				$update[self::COLUMN_UNITS] = $data[self::COLUMN_UNITS];
			}
		}
		try {
			$this->db->update(self::TABLE_INVOICE_ITEMS, $update)->where(self::COLUMN_ID, '= %i', $item_ID)->execute();
			return TRUE;
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}

	/************************************************************************************************************z*v***/
	/********** PDF **********/

	public function invoice__createPDF($invoice_ID) {
		$invoice_Data = $this->invoice__data($invoice_ID);
		if(!is_null($invoice_Data)) {
			return $this->invoice__PDF($invoice_Data);
		}
		return FALSE;
	}

	private function invoice__PDF($invoice_Data) {
		require_once __DIR__ . '/../../../vendor/mpdf/mpdf/mpdf.php';
		$file_Name = sprintf('%s_%s', $invoice_Data['number'], !is_null($invoice_Data['contractor']) ? $invoice_Data['contractor']['name'] : NULL);
		$latte = new Engine();
		$params = [
			'invoice' => $invoice_Data,
		];
		$stylesheet = file_get_contents(__DIR__ . '/templates/css/pdf.css');
		$template = $latte->renderToString(__DIR__ . '/templates/invoice.latte', $params);
		$pdf = new PdfResponse($template);
		$pdf->pageFormat = 'A4';
		$pdf->pageMargins = '0,0,0,0,0,0';
		$pdf->getMPDF()->WriteHTML($stylesheet, 1);
		$pdf->save($this->config['path']['invoice']['pdf'], $file_Name);
		//$pdf->setSaveMode(PdfResponse::INLINE);
		return $file_Name;
	}
}