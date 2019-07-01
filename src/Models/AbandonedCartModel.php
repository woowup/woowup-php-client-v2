<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;

class AbandonedCartModel implements \JsonSerializable
{
    private $service_uid;
    private $email;
    private $document;
    private $total_price;
    private $external_id;
    private $source;
    private $recovered;
    private $recover_url;
    private $products = [];
    private $createtime;

    // data cleanser
    private $cleanser;
    
    public function __construct()
    {
        foreach (get_object_vars($this) as $key => $value) {
            unset($this->{$key});
        }

        $this->cleanser = new DataCleanser();

        return $this;
    }

    public function validate()
    {
        if (!isset($this->external_id) || empty($this->external_id)) {
            throw new \Exception("Invalid Abandoned Cart: invalid external_id", 1);
            return false;
        }

        if (!isset($this->products) || empty($this->products) || !is_array($this->products) || !$this->validateProducts($this->products)) {
            throw new \Exception("Invalid Abandoned Cart " . $this->external_id . ": invalid products", 1);
            return false;
        }

        if (!isset($this->createtime) || empty($this->createtime)) {
            throw new \Exception("Invalid Abandoned Cart " . $this->external_id . ": invalid createtime", 1);
            return false;
        }

        if (isset($this->service_uid) && !empty($this->service_uid)) {
            return true;
        }

        if (isset($this->document) && !empty($this->document)) {
            return true;
        }

        if (isset($this->email) && !empty($this->email)) {
            return true;
        }

        throw new \Exception("Invalid Abandoned Cart " . $this->external_id . ": at least service_uid, document or email must be specified", 1);
        return false;
    }

    /**
     * @return mixed
     */
    public function getServiceUid()
    {
        return $this->service_uid;
    }

    /**
     * @param mixed $service_uid
     *
     * @return self
     */
    public function setServiceUid(string $service_uid)
    {
        if ((is_string($service_uid) && (strlen($service_uid) > 0)) || is_null($service_uid)) {
            $this->service_uid = $service_uid;
        } else {
            trigger_error("Invalid service_uid", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     *
     * @return self
     */
    public function setEmail(string $email, $sanitize = true)
    {
        if ((is_string($email) && (strlen($email) > 0)) || is_null($email)) {
            if ($sanitize) {
                if (($email = $this->cleanser->email->sanitize($email)) === false) {
                    trigger_error("Email sanitization of $email failed", E_USER_WARNING);
                    return $this;
                }
            }
            $this->email = $email;
        } else {
            trigger_error("Invalid email", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param mixed $document
     *
     * @return self
     */
    public function setDocument(string $document)
    {
        if ((is_string($document) && (strlen($document) > 0)) || is_null($document)) {
            $this->document = $document;
        } else {
            trigger_error("Invalid document", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalPrice()
    {
        return $this->total_price;
    }

    /**
     * @param mixed $total_price
     *
     * @return self
     */
    public function setTotalPrice(float $total_price)
    {
        $this->total_price = $total_price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * @param mixed $external_id
     *
     * @return self
     */
    public function setExternalId(string $external_id)
    {
        if (empty($external_id)) {
            trigger_error("external_id cannot be empty", E_USER_WARNING);
        } else {
            $this->external_id = $external_id;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     *
     * @return self
     */
    public function setSource(string $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecovered()
    {
        return $this->recovered;
    }

    /**
     * @param mixed $recovered
     *
     * @return self
     */
    public function setRecovered($recovered)
    {
        $this->recovered = $recovered;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecoverUrl()
    {
        return $this->recover_url;
    }

    /**
     * @param mixed $recover_url
     *
     * @return self
     */
    public function setRecoverUrl(string $recover_url)
    {
        $this->recover_url = $recover_url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param mixed $products
     *
     * @return self
     */
    public function setProducts(array $products, $prettify = true)
    {
        if ($this->validateProducts($products)) {
            $this->products = $products;
        } else {
            trigger_error("Invalid products", E_USER_WARNING);
        }

        return $this;
    }

    public function addProduct($product)
    {
        if (is_array($product)) {
            $product = (object) $product;
        }
        if (!isset($product->sku) || empty($product->sku)) {
            trigger_error("Invalid product sku", E_USER_WARNING);
        } else {
            $this->products[] = $product;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatetime()
    {
        return $this->createtime;
    }

    /**
     * @param mixed $createtime
     *
     * @return self
     */
    public function setCreatetime(string $createtime)
    {
        $this->createtime = $createtime;

        return $this;
    }

    public function jsonSerialize()
    {
        $array = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (isset($value) && ($property !== 'cleanser')) {
                $array[$property] = $value;
            }
        }

        return $array;
    }

    public static function createFromJson($json)
    {
        $abandonedCart = new self();

        foreach (json_decode($json) as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            switch ($key) {
                default:
                    if (isset($value) && !empty($value)) {
                        $abandonedCart->{$key} = $value;
                    }
                    break;
            }
        }

        return $abandonedCart;
    }

    private function validateProducts(array $products)
    {
        foreach ($products as $product) {
            if (is_array($product)) {
                $product = (object) $product;
            }
            if (!isset($product->sku) || empty($product->sku)) {
                trigger_error("Invalid sku for product", E_USER_WARNING);
                return false;
            }
        }

        return true;
    }
}
