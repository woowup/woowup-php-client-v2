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

    /**
     * The account's multi-id settings (GET /account → payload.multidentificator_settings), keyed by
     * identifier, e.g. ['document' => ['type' => 'hard'], 'email' => ['type' => 'soft']]. Disabled
     * identifiers are not part of the object, so a key being present means it is enabled.
     *
     * An empty object is treated as unreadable too: an account always has at least one identifier,
     * so an empty value says nothing about service_uid and must not lead to dropping it.
     *
     * @return array|null null when the response is not a 200 or the settings are missing, not an
     *                    array or empty
     */
    public function multidentificatorSettings()
    {
        $response = $this->get($this->host . '/account', []);

        if ($response->getStatusCode() != Endpoint::HTTP_OK) {
            return null;
        }

        $data     = json_decode((string) $response->getBody(), true);
        $settings = isset($data['payload']['multidentificator_settings']) ? $data['payload']['multidentificator_settings'] : null;

        return is_array($settings) && !empty($settings) ? $settings : null;
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
