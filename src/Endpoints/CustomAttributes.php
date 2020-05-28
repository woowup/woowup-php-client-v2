<?php
namespace WoowUpV2\Endpoints;

class CustomAttributes extends Endpoint
{
	const ENTITIES = [
		'customers' 	=> 'custom-attributes',
		'purchases'		=> 'purchase-custom-attributes',
		'purchase-item' => 'purchase-item-custom-attributes',
		'products'		=> 'product-custom-attributes',
	];

    public function __construct($host, $apikey)
    {
        parent::__construct($host, $apikey);
    }


    public function getCustomAttributesDefinition($entity = 'customers')
    {
        $response = $this->get($this->host . '/account/' . self::ENTITIES[$entity], []);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            if (isset($data->payload)) {
                return $data->payload;
            }
        }

        return false;
    }

    public function updateAttributeDefinition($data, $entity)
    {
    	$response = $this->put($this->host . '/account/' . self::ENTITIES[$entity] . '/' . $data->name, $data);

    	return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;

    }

    public function create(\WoowUpV2\Models\CustomAttributeModel $customAttribute, $entity)
    {
        if ($customAttribute->validate()) {
            $response = $this->post($this->host.'/account/'.self::ENTITIES[$entity], $customAttribute);

            return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
        }

        throw new \Exception("Custom Attribute is not valid", 1);
    }
}

