<?php

namespace App\WebModule\InvoiceModule\Presenters;

use App\WebModule\Control\Invoice;
use Nette;
use App\Model;

abstract class BasePresenter extends \App\WebModule\Presenters\BasePresenter
{
	/**************************************************************************************************************z*v*/
	/******************* CONTROLS - INVOICE *******************/

	public function createComponentInvoiceList() {
		$control = new Invoice\InvoiceList($this->invoiceModel);
		return $control;
	}

	public function createComponentInvoice() {
		$control = new Invoice\Invoice($this->invoiceModel);
		return $control;
	}
}