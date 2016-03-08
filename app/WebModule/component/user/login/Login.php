<?php
namespace App\WebModule\Control\User;

use App\Config\Routes;
use App\Control\User\Login_Form;
use App\Model\User;
use App\Helper;
use App\Model\User\UserModel;
use Kdyby\Translation\Translator;
use Nette\Application\UI;
use Nette\Security\AuthenticationException;

class Login extends UI\Control
{
	/** @var UserModel */
	private $userModel;

	public function __construct(UserModel $userModel) {
		$this->userModel = $userModel;
	}

	public function render() {
		$this->template->setFile(__DIR__ . '/Login.latte');
		$this->template->render();
	}

	/**************************************************************************************************************z*v*/
	/******************* FORMS *******************/

	protected function createComponentLogin() {
		/** @var Login_Form */
		$form = new Login_Form($this->userModel, TRUE);
        $form->snippets = ['login'];
		$form->onSuccess[] = [$this, 'login_Submitted'];
		return $form;
	}

	public function login_Submitted(UI\Form $form) {
		$values = $form->getValues();
		try {
			$this->presenter->user->login($values['email'], $values['password']);
			$this->presenter->redirect(Routes::HOME_PAGE);
		} catch (AuthenticationException $e) {
			$this->flashMessage('Uživatelské jméno či heslo není správné, zkuste to, prosím, znovu.', 'error');
            if($this->presenter->isAjax()) {
                $this->redrawControl('login');
            }
            else {
                $this->presenter->redirect('this');
            }
		}
	}
}