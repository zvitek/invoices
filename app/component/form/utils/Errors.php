<?php
namespace App\Control\Form;

use Nette\Application\UI;

class Errors extends UI\Control
{
	public function __construct() {
		parent::__construct();
	}

	public function render($form) {
		$this->template->setFile(__DIR__ . '/Errors.latte');
		$this->template->form = $form;
		$this->template->render();
	}
}