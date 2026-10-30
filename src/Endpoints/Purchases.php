<?php
namespace WoowUpV2\Endpoints;

/**
*
*/
class Purchases extends Endpoint
{
    public function __construct($host, $apikey, ?\GuzzleHttp\ClientInterface $http = null)
    {
        parent::__construct($host, $apikey, $http);
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

    /**
     * Saves the customer and queues the sale. A 200 confirms the customer, not the sale: if the
     * sale fails later in the queue it is reported through the account's failed-purchase webhook.
     * Errors (bad_request, error_branch_name, duplicated_purchase_number, customer_not_saved)
     * surface as a RequestException, same as create().
     *
     * @return \stdClass payload with userapp_id and customer_created
     */
    public function createWithCustomer(\WoowUpV2\Models\PurchaseModel $purchase)
    {
        $purchase->validateWithCustomer();

        $response = $this->post($this->host . '/purchases/with-customer', $purchase);

        return json_decode($response->getBody())->payload;
    }

    /**
     * Each sale is validated and saved on its own. A partial save answers 207 and does not throw:
     * the sales that did not go in come in `errors`, by invoice_number with their code, and only
     * those should be resent. When none goes in the API answers 400, which surfaces as a
     * RequestException with the same `errors` in its body.
     *
     * @param \WoowUpV2\Models\PurchaseModel[] $purchases
     * @return \stdClass payload with `customers` and, on a partial save, `errors`
     */
    public function bulkCreateWithCustomer($purchases)
    {
        foreach ($purchases as $key => $purchase) {
            if (!is_a($purchase, "\WoowUpV2\Models\PurchaseModel")) {
                throw new \Exception("Purchase at key $key is not valid", 1);
            }
            $purchase->validateWithCustomer();
        }

        $response = $this->post($this->host . '/purchases/bulk/with-customer', array_values($purchases));

        return json_decode($response->getBody())->payload;
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

    /**
     * The API answers at most one purchase per invoice number. When the same invoice exists in
     * several branches, pass `branch_id` in $params to get the one from that branch.
     */
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

    /**
     * Soft deletes a purchase. Without a branch name the API resolves the invoice number alone and
     * deletes the first match from any branch, so callers that know the branch must pass it.
     * Deleting an already deleted purchase responds 404, which surfaces as a RequestException.
     *
     * @param string|int  $invoiceNumber
     * @param string|null $branchName
     * @return bool
     */
    public function delete($invoiceNumber, $branchName = null)
    {
        $body = ['invoice_number' => $invoiceNumber];

        if (!empty($branchName)) {
            $body['branch_name'] = $branchName;
        }

        $response = $this->deleteJson($this->host . '/purchases', $body);

        return $response->getStatusCode() == Endpoint::HTTP_OK;
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
