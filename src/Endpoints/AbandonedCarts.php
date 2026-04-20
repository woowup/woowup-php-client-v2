<?php
namespace WoowUpV2\Endpoints;

/**
 *
 */
class AbandonedCarts extends Endpoint
{
    public function __construct($host, $apikey, \GuzzleHttp\ClientInterface $http = null)
    {
        parent::__construct($host, $apikey, $http);
    }

    public function create(\WoowUpV2\Models\AbandonedCartModel $cart)
    {
        if (!$cart->validate()) {
            throw new \Exception("Abandoned Cart is not valid", 1);
        }

        $response = $this->post($this->host . '/multiusers/abandoned-cart', $cart);

        return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
    }
}
