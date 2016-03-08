<?php
namespace App\Control;

use Nette\Application\UI;

class BaseControl extends UI\Control
{
	public function __construct() {
		parent::__construct();
	}

	public function redraw($components, $redirect = 'this') {
		if($this->presenter->isAjax()) {
			if(is_array($components)) {
				foreach($components as $component) {
					$this->do_redraw($component);
				}
			}
			else {
				$this->do_redraw($components);
			}
		}
		else {
			$this->presenter->redirect($redirect);
		}
	}

	private function do_redraw($components) {
		if(strpos($components, '.') !== FALSE) {
			$components_e = explode('.', $components);
			$snippet = end($components_e);
			array_pop($components_e);
			$structure = $this;
			foreach($components_e as $component) {
				$structure->$component;
			}
			if(method_exists($structure, 'redrawControl')) {
				$structure->redrawControl($snippet);
			}
		}
		$this->redrawControl($components);
	}
}