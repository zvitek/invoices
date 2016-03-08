<?php
namespace App\Control\Form;

use Nette\Application\UI;
use App\Helper;

class BaseForm extends UI\Form
{
	/** @var array */
	public $snippets = [];

	public function redrawSnippets() {
		if($this->presenter->isAjax() && count($this->snippets)) {
			foreach($this->snippets as $snippet) {
				$this->parent->redrawControl($snippet);
			}
		}
	}
}