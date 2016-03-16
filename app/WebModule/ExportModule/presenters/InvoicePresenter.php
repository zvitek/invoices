<?php

namespace App\WebModule\ExportModule\Presenters;

use App\Config\Routes;
use App\WebModule\Control\Invoice;
use Nette;
use App\Model;

class InvoicePresenter extends BasePresenter
{
	public function actionPdf($invoice_Token) {
		$invoice_ID = $this->invoiceModel->invoice_byToken__id($invoice_Token);
		if(is_null($invoice_ID)) {
			$this->handle_Error();
		}
		$do_Open = $this->getParameter('open');
		$do_Render = $this->getParameter('render');
		$do_Regenerate = $this->getParameter('regenerate');
		$do_Generate = $this->getParameter('generate');
		$do_Show = $this->getParameter('show');

		if(!is_null($do_Render)) {
			$this->invoiceModel->invoice__createPDF($invoice_ID, TRUE);
			$this->terminate();
		}
		if(!is_null($do_Regenerate) || !is_null($do_Generate)) {
			$this->invoiceModel->invoice__createPDF($invoice_ID);
		}
		if(!is_null($do_Show)) {
			$pdf = $this->invoiceModel->invoice__createPDF($invoice_ID, FALSE, TRUE);
			$this->sendResponse($pdf);
		}
		$invoice_Data = $this->invoiceModel->invoice__data($invoice_ID);
		if(is_null($invoice_Data)) {
			$this->handle_Error();
		}
		if(!is_null($invoice_Data['invoice'])) {
			if(!is_null($do_Open)) {
				$this->redirectUrl($invoice_Data['invoice']['full_path']);
			}
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header('Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename=' . $invoice_Data['invoice']['file_name'] . '.pdf');
			readfile($invoice_Data['invoice']['document_path']);
		}
		else {
			$this->handle_Error();
		}
		$this->terminate();
	}


}