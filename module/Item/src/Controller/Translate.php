<?php

namespace Item\Controller;

use Google\Cloud\Translate\TranslateClient;

class Translate {
	const API_KEY = 'AIzaSyAS4sq0BNYhgy_hAYDFFc3TT-WxIgUDCqE';
	private $Translator;

	public function __construct() {
		$this->Translator = new TranslateClient(["key" => Translate::API_KEY]);
	}

	/**
	 * @param $Struct
	 * @param $type
	 * @throws \Couchbase\Exception
	 */
	public static function toEng(&$Struct) {
		$Translate = new Translate();
		if (!$Translate::is_assoc($Struct))
			$Translate->fromListES($Struct);
		else
			$Translate->fromObjectES($Struct);
	}

	/**
	 * @param $Struct
	 * @throws \Couchbase\Exception
	 */
	public static function toES(&$Struct) {
		$Translate = new Translate();
		$Translate->fromObjectEN($Struct);
	}

	public static function errorToEng(&$Error) {
		$Translate = new TRanslate();
		$Translate->toTraslate($Error['message']);
	}

	/**
	 * @param $Response
	 * @throws \Couchbase\Exception
	 */
	protected function fromListES(&$Response) {
		foreach ($Response as $index => $row) {
			$this->fromObjectES($row);
			$Response[$index] = $row;
		}
	}

	/**
	 * @param $Object
	 * @throws \Couchbase\Exception
	 */
	protected function fromObjectES(&$Object) {
		$Exch = new Exchange();

		$this->toTraslate($Object['name']);

		!empty($Object['description']) ? $this->toTraslate($Object['description']) : NULL;
		!empty($Object['category']) ? $this->toTraslate($Object['category']['name']) : NULL;

		foreach ($Object['price'] as $item => $value) {
			!empty($Object['price'][$item]['name']) ? $this->toTraslate($Object['price'][$item]['name']) : NULL;
			$Exch->toUSD($Object['price'][$item]['price']);
		}
	}

	/**
	 * @param $Object
	 * @throws \Couchbase\Exception
	 */
	protected function fromObjectEN(&$Object) {
		$Exch = new Exchange();
		$this->toTraslate($Object['name'], 'en', 'es');

		!empty($Object['description']) ? $this->toTraslate($Object['description'], 'en', 'es') : NULL;
		!empty($Object['category']) ? $this->toTraslate($Object['category']['name'], 'en', 'es') : NULL;

		foreach ($Object['price'] as $item => $value) {
			!empty($Object['price'][$item]['name']) ? $this->toTraslate($Object['price'][$item]['name']) : NULL;
			$Exch->toCOP($Object['price'][$item]['price']);
		}
	}

	/**
	 * @param string $txt
	 * @param string $src
	 * @param string $trg
	 * @return array|null
	 */
	protected function toTraslate(&$txt, $src = 'es', $trg = 'en') {
		$IA_Result = $this->Translator->translate($txt, [
			'source' => $src,
			'target' => $trg,
			'model'  => 'nmt'
		]);
		$txt       = $IA_Result['text'];
		return $IA_Result;
	}

	public static function is_assoc($var) {
		return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
	}
}