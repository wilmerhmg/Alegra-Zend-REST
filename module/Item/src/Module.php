<?php

namespace Item;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
/*use Zend\ModuleManager\Feature\FormElementProviderInterface;*/

class Module implements ConfigProviderInterface {
	public function getConfig() {
		return include __DIR__ . '/../config/module.config.php';
	}

	/*public function getControllerConfig() {
		return [
			'factories' => [
				Controller\ItemController::class => function ($container) {
					return new Controller\ItemController($container);
				}
			]
		];
	}*/
}