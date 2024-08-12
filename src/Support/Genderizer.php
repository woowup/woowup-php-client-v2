<?php

namespace WoowUpV2\Support;

/**
 * Genderizer Class
 *
 * This class uses the Genderize API to determine the gender
 * of a person based on their first name.
 */
class Genderizer {
    /** @var string Genderize API URL */
	const API_URL = 'https://genderize.woowup.com/customer';
    /** @var float Minimum probability threshold to consider the response as valid */
	const PROBABILITY_THRESHOLD = 0.75;
    /** @var int Minimum sample count threshold to consider the response as valid */
    const COUNT_THRESHOLD = 10;
    /** @var string Default value when gender is not recognized */
    const UNKNOWN_GENDER = 'unknown';
    /** @var array Cache to store query results */
    private $cache = [];

	public function __construct()
	{
		return $this;
	}

    /**
     * Get the gender based on the name.
     *
     * @param string $name Full name of the person.
     * @return string|false Detected gender ('M' for male, 'F' for female) or false in case of error.
     */
    public function getGender($name)
    {
        $firstName = $this->extractFirstName($name);

        return $this->cache[$firstName] ?? $this->fetchGender($firstName);
    }

    protected function extractFirstName($name)
    {
        return preg_replace("/[^a-zA-ZáéíóúÁÉÍÓÚñÑ]/u", "", strtok($name, ' '));
    }

    /**
     * Query the gender from the API.
     *
     * @param string $firstName First name to query.
     * @return string|false Detected gender ('M' for male, 'F' for female) or false in case of error.
     */
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

    /**
     * Perform a cURL request to the API.
     *
     * @param string $url URL of the request.
     * @return object|false Decoded API response or false in case of error.
     */
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

    /**
     * Check if the API response is valid.
     *
     * @param object $response Decoded API response.
     * @return bool True if the response is valid, false otherwise.
     */
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