<?php

namespace WoowUpV2\Models;

/**
 *
 */
class PurchasePricesModel implements \JsonSerializable
{
    private $cost;
    private $shipping;
    private $gross;
    private $tax;
    private $discount;
    private $total;

    public function __construct()
    {
        foreach (get_object_vars($this) as $key => $value) {
            unset($this->{$key});
        }

        return $this;
    }

    public function validate()
    {
        if (isset($this->total) && is_numeric($this->total)) {
            throw new \Exception("Invalid prices: invalid total", 1);
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param mixed $cost
     *
     * @return self
     */
    public function setCost(float $cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param mixed $shipping
     *
     * @return self
     */
    public function setShipping(float $shipping)
    {
        $this->shipping = $shipping;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGross()
    {
        return $this->gross;
    }

    /**
     * @param mixed $gross
     *
     * @return self
     */
    public function setGross(float $gross)
    {
        $this->gross = $gross;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param mixed $tax
     *
     * @return self
     */
    public function setTax(float $tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param mixed $discount
     *
     * @return self
     */
    public function setDiscount(float $discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     *
     * @return self
     */
    public function setTotal(float $total)
    {
        $this->total = $total;

        return $this;
    }

    public function jsonSerialize()
    {
        $array = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (isset($value) && !empty($value)) {
                $array[$property] = $value;
            }
        }

        return $array;
    }

    public static function createFromJson($json)
    {
        $prices = new self();

        foreach (json_decode($json, true) as $key => $value) {
            if (in_array($key, array_keys(get_class_vars(get_class($prices))))) {
                $prices->{$key} = $value;
            }
        }

        return $prices;
    }
}
