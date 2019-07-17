<?php

namespace WoowUpV2\Models;

use WoowUpV2\Models\ProductModel as WoowUpProduct;

class PurchaseItemModel extends WoowUpProduct
{
    private $product_name;
    private $quantity;
    private $unit_price;
    private $variations;
    private $manufacturer_warranty_date;
    private $extension_warranty_date;
    private $with_extension_warranty;

    public function __construct()
    {
        foreach (get_object_vars($this) as $key => $value) {
            unset($this->{$key});
        }

        parent::__construct();
        
        return $this;
    }

    public function validate()
    {
        if (!$this->getSku()) {
            throw new \Exception("Invalid product : invalid sku", 1);
            return false;
        }

        if (!isset($this->quantity) || empty($this->quantity)) {
            throw new \Exception("Invalid product " . $this->getSku() . ": invalid quantity", 1);
            return false;
        }

        if (!isset($this->unit_price) || empty($this->unit_price)) {
            throw new \Exception("Invalid product " . $this->getSku() . ": invalid unit_price", 1);
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        return $this->product_name;
    }

    /**
     * @param mixed $product_name
     *
     * @return self
     */
    public function setProductName($product_name, $prettify = true)
    {
        parent::setName($product_name, $prettify);
        $this->product_name = $prettify ? $this->cleanser->names->prettify($product_name) : $product_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     *
     * @return self
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUnitPrice()
    {
        return $this->unit_price;
    }

    /**
     * @param mixed $unit_price
     *
     * @return self
     */
    public function setUnitPrice(float $unit_price)
    {
        $this->unit_price = $unit_price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVariations()
    {
        return $this->variations;
    }

    /**
     * @param mixed $variations
     *
     * @return self
     */
    public function setVariations($variations)
    {
        $this->variations = $variations;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getManufacturerWarrantyDate()
    {
        return $this->manufacturer_warranty_date;
    }

    /**
     * @param mixed $manufacturer_warranty_date
     *
     * @return self
     */
    public function setManufacturerWarrantyDate($manufacturer_warranty_date)
    {
        $this->manufacturer_warranty_date = $manufacturer_warranty_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtensionWarrantyDate()
    {
        return $this->extension_warranty_date;
    }

    /**
     * @param mixed $extension_warranty_date
     *
     * @return self
     */
    public function setExtensionWarrantyDate($extension_warranty_date)
    {
        $this->extension_warranty_date = $extension_warranty_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWithExtensionWarranty()
    {
        return $this->with_extension_warranty;
    }

    /**
     * @param mixed $with_extension_warranty
     *
     * @return self
     */
    public function setWithExtensionWarranty($with_extension_warranty)
    {
        $this->with_extension_warranty = $with_extension_warranty;

        return $this;
    }

    public function jsonSerialize()
    {
        $array = parent::jsonSerialize();
        foreach (get_object_vars($this) as $property => $value) {
            if (isset($value) && !empty($value) && ($property !== 'cleanser')) {
                $array[$property] = $value;
            }
        }

        return $array;
    }

    public static function createFromJson($json)
    {
        $item = new self();

        foreach (json_decode($json, true) as $key => $value) {
            if (isset($value) || !empty($value)) {
                switch ($key) {
                    case 'product_id':
                        $item->setSku($value);
                        break;
                    case 'price':
                        $item->setUnitPrice($value);
                        break;
                    case 'product_name':
                        $item->setProductName($value);
                        break;
                    default:
                        $item->{$key} = $value;
                        break;
                }
            }
        }

        return $item;
    }
}
