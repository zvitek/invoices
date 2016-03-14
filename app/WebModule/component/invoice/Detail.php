<?php
namespace App\WebModule\Control\Invoice;

use App\Control;
use App\Model\Invoice\InvoiceModel;
use App\Model\User;
use App\Helper;
use Nette\Application\UI;

class IDetail extends UI\Control
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
		$this->template->setFile(__DIR__ . '/templates/Detail.latte');
		$this->template->invoice_Data = $invoice_Data;
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

	}

	/**************************************************************************************************************z*v*/
	/******************* CONTROLS *******************/

	public function createComponentItems() {
		$invoiceModel = $this->invoiceModel;
		$control = new UI\Multiplier(function($name) use($invoiceModel){
			$form = new IItem($invoiceModel);
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
		if($this->presenter->isAjax()) {
			$this->presenter->redrawControl('flashes');
			$this->redrawControl('items');
		}
		else {
			$this->presenter->redirect('this');
		}
	}

	public function handle__removeItem($item_ID) {
		if($this->invoiceModel->invoiceItem__remove($item_ID)) {
			$this->presenter->flashMessage('Položka odstraněna', 'success');
		}
		else {
			$this->presenter->flashMessage('Položku se nepodařilo odstranit', 'desktop');
		}
		if($this->presenter->isAjax()) {
			$this->redrawControl('items');
			$this->presenter->redrawControl('flashes');
		}
		else {
			$this->presenter->redirect('this');
		}
	}
}