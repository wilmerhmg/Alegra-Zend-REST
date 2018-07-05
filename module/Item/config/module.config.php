<?php

namespace Item;

use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
	'controllers'  => [
		'factories' => [
			Controller\ItemController::class => InvokableFactory::class,
		],
	],
	'router'       => [
		'routes' => [
			'album' => [
				'type'    => Segment::class,
				'options' => [
					'route'       => '/item[/:action[/:id]]',
					'constraints' => [
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id'     => '[0-9]+',
					],
					'defaults'    => [
						'controller' => Controller\ItemController::class,
						'action'     => 'index',
					],
				],
			],
		],
	],
	'view_manager' => [
		'doctype'             => 'HTML5',
		'strategies'          => [
			'ViewJsonStrategy',
		],
		/*'template_map'        => [
			'layout/layout' => __DIR__ . '/../view/layout/layout.phtml'
		],*/
		'template_path_stack' => [
			'item' => __DIR__ . '/../view',
		],
	],


];