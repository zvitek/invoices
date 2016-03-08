<?php
namespace App\WebModule\Control\Invoice;

use App\Model\Invoice;
use App\Model\Invoice\InvoiceModel;
use App\Model\User;
use App\Helper;
use Nette\Application\UI;

class InvoiceList extends UI\Control
{
	/** @var InvoiceModel */
	private $invoiceModel;

	public function __construct(InvoiceModel $invoiceModel) {
		$this->invoiceModel = $invoiceModel;
	}

	public function render() {
		$this->template->setFile(__DIR__ . '/InvoiceList.latte');
		$this->template->invoices = $this->invoiceModel->invoice__data($this->invoiceModel->invoiceFilter__ids(), [
			Invoice\Config::STRUCTURE_DATE,
		]);
		$this->template->render();
	}

	/**************************************************************************************************************z*v*/
	/******************* FORMS *******************/
}