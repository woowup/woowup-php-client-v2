<?php
namespace WoowUpV2\Endpoints;

class Segments extends Endpoint
{
    public function __construct($host, $apikey, \GuzzleHttp\ClientInterface $http = null)
    {
        parent::__construct($host, $apikey, $http);
    }

    public function find($segmentId, $params = [])
    {
        $response = $this->get($this->host . '/segments/'.$segmentId, $params);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            return $data->payload;
        }
        return false;
    }
}