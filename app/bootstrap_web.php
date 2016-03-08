<?php
require __DIR__ . '/bootstrap.php';

$container = new Application(ApplicationMode::MODULE_WEB);
return $container->get_Container();