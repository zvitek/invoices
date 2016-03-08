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

	public function __construct(Connection $connection, User $user, BankModel $bankModel, ClientModel $clientModel, ContractorModel $contractorModel) {
		$this->db = $connection;
		$this->bankModel = $bankModel;
		$this->clientModel = $clientModel;
		$this->contractorModel = $contractorModel;
		$this->user = $user;
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
					// @TODO ADD ITEMS
					$structure['items'] = [

					];
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

	/************************************************************************************************************z*v***/
	/********** GETTER **********/
	
	public function invoiceFilter__ids() {
		$invoice_Data = $this->db->select(self::COLUMN_ID)->from(self::TABLE_INVOICES)->fetchAll();
		return count($invoice_Data) ? Data::prepare_ids($invoice_Data) : [];
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
			])->execute(\dibi::IDENTIFIER);
		}
		catch(Exception $e) {
			Debugger::log($e);
			return FALSE;
		}
	}

	/************************************************************************************************************z*v***/
	/********** PDF **********/

	public function invoice__createPDF() {
		return $this->movement__PDF();
	}

	private function movement__PDF() {
		require_once __DIR__ . '/../../../vendor/mpdf/mpdf/mpdf.php';
		$latte = new Engine();
		$params = [];
		$stylesheet = file_get_contents(__DIR__ . '/templates/css/pdf.css');

		$template = $latte->renderToString(__DIR__ . '/templates/invoice.latte', $params);
		$pdf = new PdfResponse($template);
		$pdf->pageFormat = 'A4';
		$pdf->pageMargins = '0,0,0,0,0,0';
		$pdf->getMPDF()->WriteHTML($stylesheet, 1);
		$pdf->setSaveMode(PdfResponse::INLINE);
		return $pdf;
	}
}