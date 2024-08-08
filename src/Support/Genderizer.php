<?php

namespace WoowUpV2\Support;

class Genderizer {
	const API_URL = 'https://genderize.woowup.com/';
    const ENDPOINT_CUSTOMER = 'customer';
	const PROBABILITY_THRESHOLD = 0.75;
    const COUNT_THRESHOLD = 10;
    const UNKNOWN_GENDER = 'unknown';

	public function __construct()
	{
		return $this;
	}

	public function getGender($name)
	{
		$firstName = $this->extractFirstName($name);

		$response = $this->genderize(urlencode($firstName));

        if(!$response || (isset($response->gender) && $response->gender == self::UNKNOWN_GENDER)) {
            return false;
        }

        if ($this->isResponseValid($response)) {
            return ($response->gender === "male") ? "M" : "F";
        }

        return false;
	}

    protected function extractFirstName($name)
    {
        $parts = explode(' ', $name); // Si el nombre es compuesto pruebo con el primer nombre
        return count($parts) > 1 ? array_shift($parts) : $name;
    }

    protected function genderize($name)
    {
        $curl = curl_init();

        $url = self::API_URL. self::ENDPOINT_CUSTOMER . "?first_name=$name";

        if (isset($_ENV['genderizeApikey']) && !is_null($_ENV['genderizeApikey'])) {
            $url .= "&apikey=" . $_ENV['genderizeApikey'];
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

        if (!empty($err) || (!empty($response) && isset(json_decode($response)->error))) {
            $err = !empty($err) ? $err : json_decode($response)->error;
            echo "[GENDERIZER]: cURL Error #:" . $err;
            return false;
        } else {
            $response = json_decode($response);
            return $response;
        }
    }

    protected function isResponseValid($response)
    {
        return ($response->probability >= self::PROBABILITY_THRESHOLD) && ($response->count >= self::COUNT_THRESHOLD);
    }
}