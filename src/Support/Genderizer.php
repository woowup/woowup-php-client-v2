<?php

namespace WoowUp\Support;

class Genderizer {
	const API_URL = 'https://api.genderize.io/';
	const PROBABILITY_THRESHOLD = 0.75;

	protected $apikey;

	public function __construct($apikey = null)
	{
		$this->apikey = $apikey;
	}

	public function getGender($name)
	{
		$parts = explode(' ', $name);
		if (count($parts) > 1) { // Si el nombre es compuesto pruebo con el primer nombre
			$name = array_shift($parts);
		}

		$response = $this->genderize(urlencode($name));
        if (isset($response->gender) && ($response->gender !== null) && ($response->probability >= self::PROBABILITY_THRESHOLD)) {
            return ($response->gender === "male") ? "M" : "F";
        } else {
            return false;
        }
	}

	protected function genderize($name)
	{
		$curl = curl_init();

        $url = self::API_URL . "?name=$name";

        if (isset($this->apikey) && !is_null($this->apikey)) {
            $url .= "&apikey=" . $this->apikey;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array(
                "Accept: application/json",
                "Content-Type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response = json_decode($response);
            return $response;
        }
	}
}