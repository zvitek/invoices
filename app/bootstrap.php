<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../ApplicationCommon.php';

class Application
{
	/** @var null|string  */
	private $module = NULL;

	/** @var bool */
	public $errorPresenter = TRUE;

	public function __construct($module = NULL) {
		$this->module = $module;
	}

	private function get_Configurator() {
		/** @var \Nette\Configurator */
		$configurator = new Nette\Configurator;
		$configurator->setDebugMode(ApplicationMode::debug());
		$configurator->enableDebugger(__DIR__ . '/../log');
		$configurator->setTempDirectory(__DIR__ . '/../temp');

		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->addDirectory(__DIR__ . '/../vendor')
			->register();

		$configurator->onCompile[] = function ($configurator, $compiler) {
			$compiler->addExtension('dibi', new \Dibi\Bridges\Nette\DibiExtension22());
		};

		$configurator->addConfig(__DIR__ . '/config/parameters.neon', ApplicationMode::mode());
		$configurator->addConfig(__DIR__ . '/config/config.neon', ApplicationMode::mode());

		if(!is_null($this->module)) {
			$configurator->addConfig(__DIR__ . '/config/' . $this->module . '.neon', ApplicationMode::mode());
		}

		return $configurator;
	}

	public function get_Container() {
		$container = $this->get_Configurator()->createContainer();
		if(ApplicationMode::is_production()) {
			if(!is_null($this->module) && $this->errorPresenter) {
				$container->getService('application')->errorPresenter = ucfirst($this->module) . ':Error';
			}
			$container->getService('application')->catchExceptions = TRUE;
		}
		else {
			$container->getService('application')->catchExceptions = FALSE;
		}

		return $container;
	}
}

