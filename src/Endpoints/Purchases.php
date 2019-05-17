<?php
namespace WoowUpV2\Endpoints;

/**
*
*/
class Purchases extends Endpoint
{
    function __construct($host, $apikey)
    {
        parent::__construct($host, $apikey);
    }

    public function bulkCreate($purchases)
    {
        foreach ($purchases as $key => $purchase) {
            if (!is_a($purchase, "\WoowUpV2\Models\PurchaseModel") || !$purchase->validate()) {
                throw new \Exception("Purchase at key $key is not valid", 1);
            }
        }
        $response = $this->post($this->host.'/purchases/bulk', $purchases);

        return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
    }

    public function create(\WoowUpV2\Models\PurchaseModel $purchase)
    {
        if ($purchase->validate()) {
            $response = $this->post($this->host.'/purchases', $purchase);

            return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
        }

        throw new \Exception("Purchase is not valid", 1);
    }

    public function update(\WoowUpV2\Models\PurchaseModel $purchase)
    {
        if ($purchase->validate()) {
            $response = $this->put($this->host.'/purchases', $purchase);

            return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
        }

        throw new \Exception("Purchase is not valid", 1);
    }
}
