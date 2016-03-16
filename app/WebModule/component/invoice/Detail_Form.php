<?php
namespace App\WebModule\Control\Invoice;

use App\Control;
use App\Model\Invoice\InvoiceModel;
use App\Model\User;
use App\Helper;
use Nette\Application\UI;

class IDetail_Form extends Control\BaseControl
{
	/** @var InvoiceModel */
	private $invoiceModel;

	/** @var INT */
	public $invoice_ID;

	public function __construct(InvoiceModel $invoiceModel) {
		$this->invoiceModel = $invoiceModel;
	}

	public function render() {
		$invoice_Data = $this->invoiceModel->invoice__data($this->invoice_ID);
		if(!is_null($invoice_Data)) {
			$invoice_defaults = [
				'number' => $invoice_Data['number'],
				'issue_date' => Helper\DateTime::date_Format($invoice_Data['date']['issue']),
				'date_due' => Helper\DateTime::date_Format($invoice_Data['date']['due']),
				'paid' => $invoice_Data['param']['paid'],
				'pricing' => $invoice_Data['param']['pricing'],
				'price' => $invoice_Data['price']['without_vat'],
				'price_vat' => $invoice_Data['price']['with_vat'],
				'bank_accounts_id' => is_null($invoice_Data['bank']) ? NULLL : $invoice_Data['bank']['id'],
				'contractors_id' => is_null($invoice_Data['contractor']) ? NULLL : $invoice_Data['contractor']['id'],
				'clients_id' => is_null($invoice_Data['client']) ? NULLL : $invoice_Data['client']['id'],
			];
			$this['invoice_Form']->setDefaults($invoice_defaults);
		}
		$this->template->setFile(__DIR__ . '/templates/Detail_Form.latte');
		$this->template->invoice_Data = $invoice_Data;
		$this->template->render();
	}

	/**************************************************************************************************************z*v*/
	/******************* FORMS *******************/

	protected function createComponentInvoice_Form() {
		/** @var Control\Invoice\IDetail_Form */
		$form = new Control\Invoice\IDetail_Form($this->invoiceModel);
		$form->onSuccess[] = [$this, 'invoice_Submitted'];
		return $form;
	}

	public function invoice_Submitted(UI\Form $form, $values) {
		$values['issue_date'] = Helper\DateTime::date_Database($values['issue_date']);
		$values['date_due'] = Helper\DateTime::date_Database($values['date_due']);
		if($this->invoiceModel->invoice__update($this->invoice_ID, $values)) {
			$this->presenter->flashMessage('Faktura upravena', 'success');
		}
		else {
			$this->presenter->flashMessage('Faktura se nepodařilo upravit', 'error');
		}
		if($this->presenter->isAjax()) {
			$this->presenter->redrawControl('flashes');
			$this->redrawControl('invoice_Form');
		}
		else {
			$this->presenter->redirect('this');
		}
	}

	/**************************************************************************************************************z*v*/
	/******************* CONTROLS *******************/

	public function createComponentItems() {
		$invoiceModel = $this->invoiceModel;
		$control = new UI\Multiplier(function($name) use($invoiceModel){
			$form = new IItem_Form($invoiceModel);
			$form->item_ID = $name;
			return $form;
		});
		return $control;
	}

	/**************************************************************************************************************z*v*/
	/******************* HANDLE *******************/

	public function handle__addItem() {
		if($this->invoiceModel->invoiceItem__create($this->invoice_ID)) {
			$this->presenter->flashMessage('Položka přidána', 'success');
		}
		else {
			$this->presenter->flashMessage('Položku se nepodařilo přidat', 'error');
		}
		$this->redraw(['invoice_Items', 'presenter.flashes']);
	}

	public function handle__removeItem($item_ID) {
		if($this->invoiceModel->invoiceItem__remove($item_ID)) {
			$this->presenter->flashMessage('Položka odstraněna', 'success');
		}
		else {
			$this->presenter->flashMessage('Položku se nepodařilo odstranit', 'desktop');
		}
		$this->redraw(['invoice_Items', 'presenter.flashes']);
	}

	public function handle__createPDF() {
		$this->invoiceModel->invoice__createPDF($this->invoice_ID);
	}
}