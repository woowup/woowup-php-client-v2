<?php
namespace WoowUpV2\Endpoints;

/**
 *
 */
class Products extends Endpoint
{
    public function __construct($host, $apikey)
    {
        parent::__construct($host, $apikey);
    }

    public function exist($sku)
    {
        $response = $this->get($this->host . '/products/' . $this->encode($sku) . '/exist', []);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            return isset($data->payload) && isset($data->payload->exist) && $data->payload->exist;
        }

        return false;
    }

    public function existAsync($sku) // returns promise
    {
        return $this->getAsync($this->host . '/products/' . $this->encode($sku) . '/exist', []);
    }

    public function find($sku)
    {
        $response = $this->get($this->host . '/products/' . $this->encode($sku), []);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            if (isset($data->payload)) {
                return \WoowUpV2\Models\ProductModel::createFromJson(json_encode($data->payload));
            }
        }

        return false;
    }

    public function create(\WoowUpV2\Models\ProductModel $product)
    {
        if ($product->validate()) {
            $response = $this->post($this->host . '/products', $product);

            return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
        }

        throw new \Exception("Product is not valid", 1);
    }

    public function createAsync(\WoowUpV2\Models\ProductModel $product)
    {
        if ($product->validate()) {
            return $this->postAsync($this->host . '/products', $product);
        }

        throw new \Exception("Product is not valid", 1);
    }

    public function update($sku, \WoowUpV2\Models\ProductModel $product)
    {
        if ($product->validate()) {
            $response = $this->put($this->host . '/products/' . $this->encode($sku), $product);

            if ($response->getStatusCode() == Endpoint::HTTP_OK) {
                $data = json_decode($response->getBody());

                return isset($data->payload) && isset($data->payload->exist) && $data->payload->exist;
            }

            return false;
        }

        throw new \Exception("Product is not valid", 1);
    }

    public function updateAsync($sku, \WoowUpV2\Models\ProductModel $product) // returns promise
    {
        if ($product->validate()) {
            return $this->putAsync($this->host . '/products/' . $this->encode($sku), $product);
        }

        throw new \Exception("Product is not valid", 1);
    }

    public function search($search = [], $page = 0, $limit = 100)
    {
        $params = array_merge($search, ['page' => $page, 'limit' => $limit]);

        $response = $this->get($this->host . '/products', $params);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            return isset($data->payload) ? $data->payload : [];
        }

        return false;
    }

    protected function encode($sku)
    {
        return urlencode(base64_encode($sku));
    }
}
