<?php

namespace WoowUpV2\Support;

class Genderizer {
	const API_URL = 'https://genderize.woowup.com/customer';
	const PROBABILITY_THRESHOLD = 0.75;
    const COUNT_THRESHOLD = 10;
    const UNKNOWN_GENDER = 'unknown';
    private $cache = [];

	public function __construct()
	{
		return $this;
	}

    public function getGender($name)
    {
        $firstName = $this->extractFirstName($name);

        return $this->cache[$firstName] ?? $this->fetchGender($firstName);
    }

    protected function extractFirstName($name)
    {
        return preg_replace("/[^a-zA-ZáéíóúÁÉÍÓÚñÑ]/u", "", strtok($name, ' '));
    }

    private function fetchGender($firstName)
    {
        $url = self::API_URL . "?first_name=" . urlencode($firstName);
        $url .= !empty($_ENV['genderizeApikey']) ? "&apikey=" . $_ENV['genderizeApikey'] : '';

        $response = $this->makeCurlRequest($url);

        if ($response && $this->isValidResponse($response)) {
            $gender = ($response->gender === "male") ? "M" : "F";
            $this->cache[$firstName] = $gender;
            return $gender;
        }

        return false;
    }

    private function makeCurlRequest($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Accept: application/json",
                "Content-Type: application/json",
            ],
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);

        curl_close($curl);

        if (!empty($error) || (!empty($response) && isset(json_decode($response)->error))) {
            $error = !empty($error) ? $error : json_decode($response)->error;
            echo "[GENDERIZER]: cURL Error #:" . $error;
            return false;
        }

        return json_decode($response);
    }

    private function isValidResponse($response)
    {
        return isset($response->gender) &&
            !$this->isUnknownGender($response) &&
            $this->isValidProbability($response) &&
            $this->isValidCount($response);
    }

    private function isUnknownGender($response)
    {
        return $response->gender === self::UNKNOWN_GENDER;
    }

    private function isValidProbability($response)
    {
        return isset($response->probability) && $response->probability >= self::PROBABILITY_THRESHOLD;
    }

    private function isValidCount($response)
    {
        return isset($response->count) && $response->count >= self::COUNT_THRESHOLD;
    }
}