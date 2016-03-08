<?php

namespace App\WebModule\Presenters;

use App\Config\Routes;
use App\WebModule\Control\User;
use Nette;
use App\Model;

abstract class BasePresenter extends \App\Presenters\BasePresenter
{
	public function startup() {
		parent::startup();
		if(!$this->user__hasPermission() && $this->action !== 'login') {
			$this->user->logout(TRUE);
			$this->redirect(Routes::USER_LOGIN);
		}
	}

	public function user__hasPermission() {
		if($this->user->isLoggedIn()) {
			if($this->user->isInRole('admin')) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**************************************************************************************************************z*v*/
	/******************* HELPERS *******************/

	public function handle__createInvoice() {
		$invoice_Token = $this->invoiceModel->invoice__create();
		if($invoice_Token) {
			$this->redirect(Routes::INVOICE_EDIT, ['invoice_Token' => $invoice_Token]);
		}
		else {
			$this->flashMessage('Fakturu se nepodařilo založit', 'error');
			$this->redirect('this');
		}
	}

	/**************************************************************************************************************z*v*/
	/******************* CONTROLS - USER *******************/

	public function createComponentUserLogin() {
		$control = new User\Login($this->userModel);
		$control->addComponent($this->factory__flashes(), 'flashes');
		$control->addComponent($this->factory__formErrors(), 'errors');
		return $control;
	}
}
