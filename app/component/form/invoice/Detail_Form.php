<?php
namespace App\Control\Invoice;

use App\Config;
use App\Control\Form\BaseForm;
use App\Model\Invoice\InvoiceModel;

class IDetail_Form extends BaseForm
{
	/** @var InvoiceModel */
	private $invoiceModel;

	public function __construct(InvoiceModel $invoiceModel, $ajax = TRUE) {
		parent::__construct();
		$this->invoiceModel = $invoiceModel;
		if($ajax) {
			$this->getElementPrototype()->class = 'ajax';
		}

		$this->addText('number', 'Číslo faktury')
			->addRule(self::FILLED, 'Zadejte číslo faktury');

		$this->addText('issue_date', 'Datum vystavení')
			->addRule(self::FILLED, 'Zadejte datum vystavení');

		$this->addText('date_due', 'Datum splatnosti')
			->addRule(self::FILLED, 'Zadejte datum splatnosti');

		$this->addText('price', 'Cena')
			->addRule(self::FILLED, 'zadejte celkovou cenu')
			->addRule(self::NUMERIC, 'Celková cena musí být číslo');

		$this->addText('price_vat', 'Cena s DPH')
			->addCondition(self::FILLED)
				->addRule(self::FILLED, 'zadejte celkovou cenu s DPH')
				->addRule(self::NUMERIC, 'Celková cena s DPH musí být číslo');

		$this->addCheckbox('paid', 'Zaplaceno');
		$this->addCheckbox('pricing', 'Nacenění');

		$this->addSelect('bank_accounts_id', 'Bankovní účet', $this->invoiceModel->accounts__select())
			->setPrompt('vyberte')
			->addRule(self::FILLED, 'Vyberte bankovní účet');

		$this->addSelect('contractors_id', 'Dodavatel', $this->invoiceModel->contractors__select())
			->setPrompt('vyberte')
			->addRule(self::FILLED, 'Vyberte dodavatele');

		$this->addSelect('clients_id', 'Klient', $this->invoiceModel->clients__select())
			->setPrompt('vyberte')
			->addRule(self::FILLED, 'Vyberte klienta');

		$this->addSubmit('save', 'Uložit');

		$this->onValidate[] = [$this, 'invoice_Validation'];
	}

	public function invoice_Validation(BaseForm $form, $values = []) {
	}
}