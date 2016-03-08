<?php

namespace App\Presenters;

use App\Control;
use App\Control\Form;
use Nette;
use App\Model;


abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @inject
	 * @var Model\User\UserModel
	 */
	public $userModel;

	/**
	 * @inject
	 * @var Model\Invoice\InvoiceModel
	 */
	public $invoiceModel;

	/**
	 * @inject
	 * @var Model\Invoice\BankModel
	 */
	public $bankModel;

	/**
	 * @inject
	 * @var Model\Invoice\ClientModel
	 */
	public $clientModel;

	/**
	 * @inject
	 * @var Model\Invoice\ContractorModel
	 */
	public $contractorModel;

	/** @var array */
	public $config;

	public function injectConfig() {
		$this->config = $this->context->parameters;
	}

	public function startup() {
		parent::startup();
		$panel = new \Dibi\Bridges\Tracy\Panel;
		$panel->register($this->context->getService('database'));
	}

	public function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);
		$template->config = $this->config;
		return $template;
	}

	/**************************************************************************************************************z*v*/
	/******************* HANDLE *******************/

	public function handle__logout() {
		$this->user->logout(TRUE);
		$this->redirect(Routes::HOME_PAGE);
	}


	/**************************************************************************************************************z*v*/
	/******************* CONTROLS - FACTORY *******************/

	public function factory__formErrors() {
		$control = new Form\Errors();
		return $control;
	}

	public function factory__flashes() {
		$control = new Control\Flashes();
		return $control;
	}

	/**************************************************************************************************************z*v*/
	/******************* CONTROLS *******************/

	/**
	 * @return Control\Flashes
	 */
	public function createComponentFlashes() {
		return $this->factory__flashes();
	}
}