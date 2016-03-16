<?php

namespace App\WebModule\ExportModule\Presenters;

use App\WebModule\Control\Invoice;
use Nette;
use App\Model;

abstract class BasePresenter extends \App\WebModule\Presenters\BasePresenter
{
	public function handle_Error() {
		echo 'Ajaj...';
		$this->terminate();
	}
}