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

    public function createAsync(\WoowUpV2\Models\PurchaseModel $purchase)
    {
        if ($purchase->validate()) {
            return $this->postAsync($this->host.'/purchases', $purchase);
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

    public function updateAsync(\WoowUpV2\Models\PurchaseModel $purchase)
    {
        if ($purchase->validate()) {
            return $this->putAsync($this->host.'/purchases', $purchase);
        }

        throw new \Exception("Purchase is not valid", 1);
    }

    public function find($invoiceNumber, $params = [])
    {
        $params = array_merge([
            'invoice_number' => $invoiceNumber,
        ], $params);

        $response = $this->get($this->host . '/purchases', $params);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            if (isset($data->payload) && !empty($data->payload)) {
                return \WoowUpV2\Models\PurchaseModel::createFromJson(json_encode(array_shift($data->payload)));
            }
        }

        return false;
    }

    public function findPayment($firstSixDigits)
    {
        $response = $this->get($this->host . '/purchases/iin/' . $firstSixDigits, []);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            if (isset($data->payload) || !empty($data->payload)) {
                $payment = new \WoowUpV2\Models\PurchasePaymentModel();
                $payment->setType($data->payload->type);
                $payment->setBrand($data->payload->brand);
                $payment->setBank($data->payload->bank->name);

                return $payment;
            }
        }

        return false;
    }
}
