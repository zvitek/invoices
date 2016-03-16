<?php

namespace App\WebModule\InvoiceModule\Presenters;

use App\Config\Routes;
use App\WebModule\Control\Invoice;
use Nette;
use App\Model;

class PagePresenter extends BasePresenter
{
	public function actionEdit($invoice_Token) {
		$invoice_ID = $this->invoiceModel->invoice_byToken__id($invoice_Token);
		if(is_null($invoice_ID)) {
			$this->redirect(Routes::INVOICE_LIST);
		}

		/** @var Invoice\Invoice */
		$this['invoice_Form']->invoice_ID = $invoice_ID;
	}
}