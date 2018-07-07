<?php

namespace Item\Entity;


class Item {
	public $name;
	public $price;
	public $description;
	public $reference;
	public $productKey;

	public function exchangeArray(array $data) {
		$this->name        = !empty($data['name']) ? $data['name'] : NULL;
		$this->price       = !empty($data['price']) ? $data['price'] : NULL;
		$this->description = !empty($data['description']) ? $data['description'] : NULL;
		$this->reference   = !empty($data['reference']) ? $data['reference'] : NULL;
		$this->productKey  = !empty($data['productKey']) ? $data['productKey'] : NULL;
	}
}