<?php
namespace App\Control\Invoice;

use App\Config;
use App\Control\Form\BaseForm;

class IItem_Form extends BaseForm
{
	public function __construct($ajax = TRUE) {
		parent::__construct();
		if($ajax) {
			$this->getElementPrototype()->class = 'ajax';
		}

		$this->addText('name', 'Název položky')
			->addRule(self::FILLED, 'Zadejte název položky');

		$this->addTextArea('description', 'Popis položky');

		$this->addText('price_per_unit', 'Cena za jednotku')
			->addRule(self::FILLED, 'Zadejte cenu za jednotku')
			->addRule(self::NUMERIC, 'Cena za jednotku musí být číslo');

		$this->addText('units', 'Počet')
			->setDefaultValue(1)
			->addRule(self::FILLED, 'Zadejte počet jednotek')
			->addRule(self::NUMERIC, 'Počet jednotek musí obsahovat číslo');

		$this->addText('total', 'Cena za položku')
			->addRule(self::FILLED, 'Zadejte cenu za položku')
			->addRule(self::NUMERIC, 'Cena za položku musí být číslo');

		$this->addSubmit('save', 'Uložit');

		$this->onValidate[] = [$this, 'invoiceItem_Validation'];
	}

	public function invoiceItem_Validation(BaseForm $form, $values = []) {

	}
}