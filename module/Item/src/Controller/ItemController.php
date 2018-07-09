<?php

namespace Item\Controller;

use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Validator\Digits;
use Zend\Validator\StringLength;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;

class ItemController extends AbstractActionController {
	const API_ALEGRA       = 'https://app.alegra.com/api/v1/items';
	const API_ALEGRA_TAXES = 'https://app.alegra.com/api/v1/taxes';
	const API_ALEGRA_CATS  = 'https://app.alegra.com/api/v1/categories';
	const API_ALEGRA_USER  = 'wilmer@pulsejs.io';
	const API_ALEGRA_KEY   = '52e61533fa43737aab55';

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

		if ($response->getStatusCode() == Response::STATUS_CODE_200) {
			$result['total']   = $result['metadata']['total'];
			$result['success'] = TRUE;
			Translate::toEng($result['data']);
		} elseif (isset($result['message'])) {
			Translate::errorToEng($result);
		}

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
		if ($response->getStatusCode() == Response::STATUS_CODE_200) {
			Translate::toEng($result);
			$result = ["success" => TRUE, "data" => $result];
		} elseif (isset($result['message'])) {
			Translate::errorToEng($result);
		}

		$this->getResponse()->setStatusCode($response->getStatusCode());
		return new JsonModel($result);
	}

	/**
	 * @return JsonModel
	 * @throws \Couchbase\Exception
	 */
	public function addAction() {
		$nameValidator = new StringLength(['min' => 1, 'max' => 150]);
		$descValidator = new StringLength(['min' => 0, 'max' => 500]);
		$rawBody       = $this->getRequest()->getPost()->toArray();

		if (!$nameValidator->isValid($rawBody['name']) ||
			!is_numeric($rawBody['price'][0]['price']) ||
			!$descValidator->isValid($rawBody['description'])) {
			$this->getResponse()->setStatusCode(400);
			return new JsonModel(["code" => 400, "message" => "Check all inputs"]);
		}

		$response = new Client();
		$response->setUri(ItemController::API_ALEGRA);
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('POST');

		$rawBody = $this->clean_array($rawBody);
		Translate::toES($rawBody);

		$response->setRawBody(json_encode($rawBody));
		$response = $response->send();

		$result = json_decode($response->getBody(), TRUE);
		if ($response->getStatusCode() == Response::STATUS_CODE_201) {
			Translate::toEng($result);
			$this->attachAction($result['id']);
		} elseif (isset($result['message'])) {
			Translate::errorToEng($result);
		}

		$this->getResponse()->setStatusCode($response->getStatusCode());
		return new JsonModel($result);
	}

	/**
	 * @return JsonModel
	 * @throws \Couchbase\Exception
	 */
	public function editAction() {
		$nameValidator = new StringLength(['min' => 1, 'max' => 150]);
		$descValidator = new StringLength(['min' => 0, 'max' => 500]);
		$idValidator   = new Digits();
		$rawBody       = $this->getRequest()->getPost()->toArray();
		$id            = $this->params()->fromRoute('id');
		if (!$nameValidator->isValid($rawBody['name']) ||
			!is_numeric($rawBody['price'][0]['price']) ||
			!$descValidator->isValid($rawBody['description']) ||
			!$idValidator->isValid($id)) {
			$this->getResponse()->setStatusCode(400);
			return new JsonModel(["code" => 400, "message" => "Check all inputs"]);
		}

		$response = new Client();
		$response->setUri(ItemController::API_ALEGRA . "/$id");
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('PUT');

		$rawBody = $this->clean_array($rawBody);
		Translate::toES($rawBody);

		$response->setRawBody(json_encode($rawBody));
		$response = $response->send();

		$result = json_decode($response->getBody(), TRUE);
		if ($response->getStatusCode() == Response::STATUS_CODE_200) {
			Translate::toEng($result);
			$this->attachAction($result['id']);
		} elseif (isset($result['message'])) {
			Translate::errorToEng($result);
		}
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
	public function attachAction($id = NULL) {
		$response   = new Client();
		$id         = $this->params()->fromRoute('id', $id);
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

	/**
	 * @return JsonModel
	 * @throws \Couchbase\Exception
	 */
	public function taxesAction() {
		$response = new Client();
		$response->setUri(ItemController::API_ALEGRA_TAXES);
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('GET');

		$response = $response->send();
		$result   = json_decode($response->getBody(), TRUE);

		$this->getResponse()->setStatusCode($response->getStatusCode());

		if ($response->getStatusCode() == Response::STATUS_CODE_200) {
			Translate::taxesToEng($result);
			return new JsonModel([
				"success" => TRUE,
				"data"    => $result
			]);
		} elseif (isset($result['message'])) {
			Translate::errorToEng($result);
		}
		return new JsonModel($result);
	}

	/**
	 * @return JsonModel
	 * @throws \Couchbase\Exception
	 */
	public function categoriesAction() {
		$response = new Client();
		$response->setUri(ItemController::API_ALEGRA_CATS);
		$response->setAuth(ItemController::API_ALEGRA_USER, ItemController::API_ALEGRA_KEY, Client::AUTH_BASIC);
		$response->setMethod('GET');
		$response->setParameterGet(["format" => "plain", "type" => "income"]);
		$response = $response->send();
		$result   = json_decode($response->getBody(), TRUE);

		$this->getResponse()->setStatusCode($response->getStatusCode());

		if ($response->getStatusCode() == Response::STATUS_CODE_200) {
			unset($result[0]);
			$result = array_values($result);
			Translate::categoriesToEng($result);
			return new JsonModel([
				"success" => TRUE,
				"data"    => $result
			]);
		} elseif (isset($result['message'])) {
			Translate::errorToEng($result);
		}
		return new JsonModel($result);
	}

	private function is_image($Exfit) {
		return (strpos($Exfit['type'], 'image') !== FALSE);
	}

	private function clean_array($array) {
		$array = array_filter($array, function ($v) { return !empty($v); });
		foreach ($array as $index => $value) {
			if (is_array($value)) {
				$array[$index] = $this->clean_array($value);
			}
		}
		return array_filter($array, function ($v) { return !empty($v); });
	}
}
