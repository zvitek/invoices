<?php
require __DIR__ . '/../../ApplicationMode.php';
if(ApplicationMode::maintenance(ApplicationMode::MODULE_WEB)) {
	require '.maintenance.php';
}
$container = require __DIR__ . '/../../app/bootstrap_web.php';
$container->getByType('Nette\Application\Application')->run();