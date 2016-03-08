<?php
namespace App\Control;

use Nette\Application\UI;

class Flashes extends UI\Control
{
	public function __construct() {
		parent::__construct();
	}

	public function render($flashes) {
		$this->template->setFile(__DIR__ . '/Flashes.latte');
		$this->template->flashes = $flashes;
		$this->template->render();
	}
}