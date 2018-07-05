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
		if (is_array($Struct))
			$Translate->fromListES($Struct);
		else
			$Translate->fromObjectES($Struct);
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

		foreach ($Object['price'] as $item => $value)
			$Exch->toUSD($Object['price'][$item]['price']);
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
}