<?php

namespace Item\Form\Filter;


use Zend\InputFilter\InputFilter;

class ItemFilter extends InputFilter {
	function __construct() {
		$this->add([
			'name'       => 'name',
			'required'   => TRUE,
			'validators' => [[
				'name'    => 'StringLength',
				'options' => [
					'encoding' => 'UTF-8',
					'min'      => 2,
					'max'      => 150
				]
			]]
		]);

		$this->add([
			'name'       => 'price',
			'required'   => TRUE,
			'validators' => [[
				'name' => 'Digits'
			]]
		]);

		$this->add([
			'name'       => 'description',
			'validators' => [[
				'name'    => 'StringLength',
				'options' => [
					'max' => 500
				]
			]]
		]);

		$this->add([
			'name'       => 'reference',
			'required'   => TRUE,
			'validators' => [[
				'name'    => 'StringLength',
				'options' => [
					'max' => 45
				]
			]]
		]);

		$this->add([
			'name'       => 'productKey',
			'validators' => [[
				'name'    => 'StringLength',
				'options' => [
					'min' => 8,
					'max' => 8
				]
			]]
		]);
	}
}