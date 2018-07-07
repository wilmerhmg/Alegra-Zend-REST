<?php

namespace Item\Controller;

use Zend\Form\Element\File;
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

	/**
	 * @return JsonModel
	 * @throws \Couchbase\Exception
	 */
	public function deleteAction() {
		$response = new Client();
		$id       = $this->params()->fromRoute('id');
		$response->setUri(ItemController::API_ALEGRA . "/$id");
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('DELETE');
		$response = $response->send();

		$result = json_decode($response->getBody(), TRUE);
		Translate::errorToEng($result);

		$this->getResponse()->setStatusCode($response->getStatusCode());
		return new JsonModel($result);
	}

	/**
	 * @return JsonModel
	 */
	public function attachAction() {
		$response   = new Client();
		$id         = $this->params()->fromRoute('id');
		$attachment = $this->params()->fromFiles('attachment');
		$path       = './uploads/';
		$rename     = hash('SHA1', date('Ymd') . microtime());
		$file       = $path . basename("$rename-" . $attachment['name']);

		$response->setUri(ItemController::API_ALEGRA . "/$id/attachment");
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('POST');

		if (!move_uploaded_file($attachment['tmp_name'], $file)) {
			$this->getResponse()->setStatusCode(400);
			return new JsonModel(["code" => 400, "message" => "File is required"]);
		}


		$response->setFileUpload($file, $this->is_image($attachment) ? 'image' : 'file');

		$response->send();


		$response = $response->send();
		$code     = $response->getStatusCode();
		$result   = json_decode($response->getBody(), TRUE);

		if (isset($result['code'])) {
			Translate::errorToEng($result);
		}
		$this->getResponse()->setStatusCode($code);
		unlink($file);
		return new JsonModel($result);
	}

	public function taxAction() {
		return new JsonModel(json_decode('{"success":true,"total": 3,"results":[{"idLocal":"1","idTaxReference":"1","taxReference":"IVA","name":"IVA","tax":"0.00","description":"","verboseName":"IVA (0.00%)","status":"active"},{"idLocal":"2","idTaxReference":"1","taxReference":"IVA","name":"IVA","tax":"5.00","description":"","verboseName":"IVA (5.00%)","status":"active"},{"idLocal":"3","idTaxReference":"1","taxReference":"IVA","name":"IVA","tax":"19.00","description":"","verboseName":"IVA (19.00%)","status":"active"}]}', TRUE));
	}

	private function is_image($Exfit) {
		$image_type = $Exfit['type'];
		if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) {
			return TRUE;
		}
		return FALSE;
	}
}
