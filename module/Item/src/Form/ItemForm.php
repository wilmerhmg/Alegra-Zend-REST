<?php

namespace Item\Form;


use Zend\Form\Form;
use Zend\Hydrator\Reflection as ReflectionHydrator;
use Item\Entity\Item;

class ItemForm extends Form {
	public function init() {
		parent::__construct('ItemForm');
		$this->setHydrator(new ReflectionHydrator(FALSE))->setObject(new Item());
		$this->setAttributes([
			'method' => 'post'
		]);
	}
}