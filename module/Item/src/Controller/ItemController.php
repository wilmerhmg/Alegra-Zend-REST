<?php

namespace Item\Controller;

use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Validator\Digits;
use Zend\Validator\StringLength;
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
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('GET');
		$response->setParameterGet([
			'start'           => $this->params()->fromQuery('start', 0),
			'limit'           => $this->params()->fromQuery('limit', 30),
			'order_direction' => $this->params()->fromQuery('order_direction', 'ASC'),
			'order_field'     => $this->params()->fromQuery('order_field', 'name'),
			'query'           => $this->params()->fromQuery('query', ''),
			'metadata'        => 'true'
		]);

		$response = $response->send();

		$result = json_decode($response->getBody(), TRUE);

		if ($response->getStatusCode() == Response::STATUS_CODE_200)
			Translate::toEng($result['data']);
		elseif (isset($result['message']))
			Translate::errorToEng($result);

		$this->getResponse()->setStatusCode($response->getStatusCode());

		return new JsonModel($result);
	}

	/**
	 * @return JsonModel
	 * @throws \Couchbase\Exception
	 */
	public function getAction() {
		$id = $this->params()->fromRoute('id', NULL);

		if (empty($id)) {
			$this->getResponse()->setStatusCode(404);
			return new JsonModel(["code" => 404, "message" => "The item was not registered in Alegra"]);
		}

		$response = new Client();
		$response->setUri(ItemController::API_ALEGRA . "/$id");
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('GET');

		$response = $response->send();

		$result = json_decode($response->getBody(), TRUE);
		if ($response->getStatusCode() == Response::STATUS_CODE_200)
			Translate::toEng($result);
		elseif (isset($result['message']))
			Translate::errorToEng($result);

		$this->getResponse()->setStatusCode($response->getStatusCode());
		return new JsonModel($result);
	}

	/**
	 * @return JsonModel
	 * @throws \Couchbase\Exception
	 */
	public function addAction() {
		$nameValidator  = new StringLength(['min' => 2, 'max' => 150]);
		$priceValidator = new Digits();
		$descValidator  = new StringLength(['min' => 1, 'max' => 500]);

		if (!$nameValidator->isValid($this->params()->fromPost('name')) ||
			!$priceValidator->isValid($this->params()->fromPost('price')[0]['price']) ||
			!$descValidator->isValid($this->params()->fromPost('description'))) {
			$this->getResponse()->setStatusCode(400);
			return new JsonModel(["code" => 400, "message" => "Check all inputs"]);
		}

		$response = new Client();
		$response->setUri(ItemController::API_ALEGRA);
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('POST');

		$rawBody = $this->params()->fromPost();

		Translate::toES($rawBody);

		$response->setRawBody(json_encode($rawBody));
		$response = $response->send();

		$result = json_decode($response->getBody(), TRUE);
		if ($response->getStatusCode() == Response::STATUS_CODE_201)
			Translate::toEng($result);
		elseif (isset($result['message']))
			Translate::errorToEng($result);

		$this->getResponse()->setStatusCode($response->getStatusCode());
		return new JsonModel($result);
	}

	/**
	 * @return JsonModel
	 * @throws \Couchbase\Exception
	 */
	public function editAction() {
		$nameValidator  = new StringLength(['min' => 2, 'max' => 150]);
		$priceValidator = new Digits();
		$descValidator  = new StringLength(['min' => 1, 'max' => 500]);
		$idValidator    = new Digits();
		$id             = $this->params()->fromRoute('id');
		if (!$nameValidator->isValid($this->params()->fromPost('name')) ||
			!$priceValidator->isValid($this->params()->fromPost('price')[0]['price']) ||
			!$descValidator->isValid($this->params()->fromPost('description')) ||
			!$idValidator->isValid($id)) {
			$this->getResponse()->setStatusCode(400);
			return new JsonModel(["code" => 400, "message" => "Check all inputs"]);
		}

		$response = new Client();
		$response->setUri(ItemController::API_ALEGRA . "/$id");
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('PUT');

		$rawBody = $this->params()->fromPost();

		Translate::toES($rawBody);

		$response->setRawBody(json_encode($rawBody));
		$response = $response->send();

		$result = json_decode($response->getBody(), TRUE);
		if ($response->getStatusCode() == Response::STATUS_CODE_200)
			Translate::toEng($result);
		elseif (isset($result['message']))
			Translate::errorToEng($result);

		$this->getResponse()->setStatusCode($response->getStatusCode());
		return new JsonModel($result);
	}

	public function deleteAction() {
	}
}
