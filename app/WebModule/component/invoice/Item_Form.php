<?php
namespace App\WebModule\Control\Invoice;

use App\Control;
use App\Model\Invoice\InvoiceModel;
use App\Model\User;
use App\Helper;
use Nette\Application\UI;

class IItem_Form extends UI\Control
{
	/** @var InvoiceModel */
	private $invoiceModel;

	/** @var int */
	public $item_ID;

	public function __construct(InvoiceModel $invoiceModel) {
		$this->invoiceModel = $invoiceModel;
	}

	public function render($values = NULL) {
		$item_Data = is_null($values) ? $this->invoiceModel->invoiceItem__data($this->item_ID) : $values;
		if(!is_null($item_Data)) {
			$form_defaults = [
				'name' => $values['basic']['name'],
				'description' => $values['basic']['description'],
				'price_per_unit' => $values['price']['per_unit'],
				'units' => $values['price']['units'],
				'total' => $values['price']['total'],
			];
			$this['item_Form']->setDefaults($form_defaults);
		}
		$this->template->setFile(__DIR__ . '/templates/Item_Form.latte');
		$this->template->item = $item_Data;
		$this->template->render();
	}

	/**************************************************************************************************************z*v*/
	/******************* FORMS *******************/
	
	public function createComponentItem_Form() {
		/** @var Control\Invoice\IItem_Form */
		$form = new Control\Invoice\IItem_Form(TRUE);
		$form->onSubmit[] = [$this, 'itemForm__Submitted'];
		return $form;
	}

	public function itemForm__Submitted(UI\Form $form) {
		$values = $form->getValues();
		if($this->invoiceModel->invoiceItem__update($this->item_ID, $values)) {
			$this->presenter->flashMessage('Upraveno', 'success');
		}
		else {
			$this->presenter->flashMessage('Položku se nepodařilo upravit', 'error');
		}
		if($this->presenter->isAjax()) {
			$this->presenter->redrawControl('flashes');
			$this->redrawControl('IItem');
		}
		else {
			$this->presenter->redirect('this');
		}
	}
}