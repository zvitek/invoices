<?php
namespace App\Control\User;

use App\Control\Form\BaseForm;
use App\Model\User\Config;
use App\Model\User\UserModel;

class Login_Form extends BaseForm
{
	/** @var UserModel */
	private $userModel;

	/** @var array */
	private $roles = [];

	public function __construct(UserModel $userModel, $ajax = TRUE, $roles = []) {
		parent::__construct();
		$this->userModel = $userModel;
		$this->roles = $roles;

		if($ajax) {
			$this->getElementPrototype()->class = 'ajax';
		}

		$this->addText('email', 'E-mail')
			->addRule(self::EMAIL, 'E-mail není v dobrém formátu :)');

		$this->addPassword('password', 'Heslo')
			->addRule(self::FILLED, 'Zadejte heslo');

		$this->addSubmit('submit', 'Přihlásit se');

		$this->onValidate[] = [$this, 'login_Validation'];
	}

	public function login_Validation(BaseForm $form) {
		$values = $form->getValues();
		$user = $this->userModel->user__byEmail($values[Config::COLUMN_EMAIL]);
		if(!is_null($user)) {
			if(is_null($user['registration']['activation'])) {
				$form->addError('Nejprve si aktivujte svůj účet...');
			}
			if(!$this->userModel->user__hasRoles($user['id'], $this->roles)) {
				$form->addError('Do této sekce nemáte dostatečná oprávnění.');
			}
		}
		else {
			$form->addError('Je nám líto, ale nepodařilo se nám vás přihlásit.');
		}
		$this->redrawSnippets();
	}
}