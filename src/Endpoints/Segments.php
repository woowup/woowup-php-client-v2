<?php
namespace WoowUpV2\Endpoints;

class Segments extends Endpoint
{
    public function __construct($host, $apikey)
    {
        parent::__construct($host, $apikey);
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