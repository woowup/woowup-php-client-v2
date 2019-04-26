<?php

namespace WoowUp\Models;

use WoowUp\Models\ProductModel as WoowUpProduct;

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
        return $this;
    }

    public function validate()
    {
        if (!parent::validate()) {
            return false;
        }

        if (!isset($this->product_name) || empty($this->product_name)) {
            throw new \Exception("Invalid product " . $this->sku . ": invalid product_name", 1);
            return false;
        }

        if (!isset($this->quantity) || empty($this->quantity)) {
            throw new \Exception("Invalid product " . $this->sku . ": invalid quantity", 1);
            return false;
        }

        if (!isset($this->unit_price) || empty($this->unit_price)) {
            throw new \Exception("Invalid product " . $this->sku . ": invalid unit_price", 1);
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
    public function setProductName($product_name)
    {
        parent::setName($product_name);
        $this->product_name = $product_name;

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
            if (isset($value) && !empty($value)) {
                $array[$property] = $value;
            }
        }

        return $array;
    }
}
