<?php

namespace Item\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;

class ItemController extends AbstractActionController {
	const API_ALEGRA      = 'https://app.alegra.com/api/v1/items';
	const API_ALEGRA_USER = 'wilmer@pulsejs.io';
	const API_ALEGRA_KEY  = '52e61533fa43737aab55';

	/**
	 * @return JsonModel
	 * @throws \Couchbase\Exception
	 */
	public function indexAction() {
		$response = new Client();
		$response->setUri(ItemController::API_ALEGRA);
		$response->setMethod('GET');
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('GET');
		$response->setParameterGet([
			'start'           => 0,
			'limit'           => 30,
			'order_direction' => 'ASC',
			'order_field'     => 'name',
			'query'           => '',
			'metadata'        => 'true'
		]);

		$response = $response->send();


		$result = json_decode($response->getBody(), TRUE);
		Translate::toEng($result['data']);
		return new JsonModel($result);
	}

	public function addAction() {
	}

	public function editAction() {
	}

	public function deleteAction() {
	}
}
