<?php
namespace App\WebModule\Control\Invoice;

use App\Control;
use App\Model\Invoice\InvoiceModel;
use App\Model\User;
use App\Helper;
use Nette\Application\UI;

class Invoice extends UI\Control
{
	/** @var InvoiceModel */
	private $invoiceModel;

	/** @var INT */
	public $invoice_ID;

	public function __construct(InvoiceModel $invoiceModel) {
		$this->invoiceModel = $invoiceModel;
	}

	public function render() {
		$this->template->setFile(__DIR__ . '/Invoice.latte');
		$this->template->render();
	}

	public function render_Edit() {
		$invoice_Data = $this->invoiceModel->invoice__data($this->invoice_ID);
		if(!is_null($invoice_Data)) {
			$invoice_defaults = [
				'number' => $invoice_Data['number'],
				'issue_date' => Helper\Date::dFormat($invoice_Data['date']['issue']),
				'date_due' => Helper\Date::dFormat($invoice_Data['date']['due']),
				'paid' => $invoice_Data['param']['paid'],
				'pricing' => $invoice_Data['param']['pricing'],
			];
			$this['invoice_Form']->setDefaults($invoice_defaults);
		}
		$this->template->setFile(__DIR__ . '/InvoiceEdit.latte');
		$this->template->render();
	}

	/**************************************************************************************************************z*v*/
	/******************* FORMS *******************/

	protected function createComponentInvoice_Form() {
		/** @var Control\Invoice\Invoice_Form */
		$form = new Control\Invoice\Invoice_Form($this->invoiceModel);
		$form->onSuccess[] = [$this, 'invoice_Submitted'];
		return $form;
	}

	public function invoice_Submitted(UI\Form $form, $values) {

	}
}