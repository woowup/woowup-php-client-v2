<?php
namespace WoowUpV2\Endpoints;

/**
 *
 */
class Banks extends Endpoint
{
    public function __construct($host, $apikey)
    {
        parent::__construct($host, $apikey);
    }

    public function getDataFromFirstDigits(string $firstSixDigits)
    {
        $response = $this->get($this->host . '/purchases/iin/'.$firstSixDigits);

        return $response->getStatusCode() == Endpoint::HTTP_OK ? $response->getBody() : null;
    }

    public function getDataFromFirstDigitsAsync(string $firstSixDigits)
    {
        return $this->getAsync($this->host . '/purchases/iin/'.$firstSixDigits);
    }
}
