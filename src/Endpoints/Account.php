<?php
namespace WoowUpV2\Endpoints;

/**
 *
 */
class Account extends Endpoint
{
    protected $appId;
    public function __construct($host, $apikey, $http = null, $appId = null)
    {
        parent::__construct($host, $apikey, $http);
        $this->appId = $appId;
    }

    public function customAttributes()
    {
        $response = $this->get($this->host . '/account/custom-attributes', []);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
        	$data = json_decode($response->getBody());

        	return $data->payload;
        }
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getApiKey(): string
    {
        return $this->apikey;
    }
}
