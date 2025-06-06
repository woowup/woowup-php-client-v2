<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;
use WoowUpV2\Models\CategoryModel;

class ProductModel implements \JsonSerializable
{
    private $id;
    private $sku;
    private $name;
    private $base_name;
    private $brand;
    private $description;
    private $url;
    private $image_url;
    private $thumbnail_url;
    private $price;
    private $offer_price;
    private $stock;
    private $available;
    private $category = [];
    private $specifications = [];
    private $metadata;
    private $createtime;
    private $updatetime;
    private $custom_attributes;

    // data cleanser
    protected $cleanser;

    public function __construct()
    {
        foreach (get_object_vars($this) as $key => $value) {
            unset($this->{$key});
        }

        $this->cleanser = new DataCleanser();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return isset($this->sku) ? $this->sku : null;
    }

    /**
     * @param mixed $sku
     *
     * @return self
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return self
     */
    public function setName($name, $prettify = true)
    {
        $this->name = $prettify ? $this->cleanser->names->prettify($name) : $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseName()
    {
        return $this->base_name;
    }

    /**
     * @param mixed $base_name
     *
     * @return self
     */
    public function setBaseName($base_name, $prettify = true)
    {
        $this->base_name = $prettify ? $this->cleanser->names->prettify($base_name) : $base_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param mixed $brand
     *
     * @return self
     */
    public function setBrand($brand, $prettify = true)
    {
        $this->brand = $prettify ? $this->cleanser->names->prettify($brand) : $brand;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }

    /**
     * @param mixed $image_url
     *
     * @return self
     */
    public function setImageUrl($image_url)
    {
        $this->image_url = $image_url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnail_url;
    }

    /**
     * @param mixed $thumbnail_url
     *
     * @return self
     */
    public function setThumbnailUrl($thumbnail_url)
    {
        $this->thumbnail_url = $thumbnail_url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param mixed $stock
     *
     * @return self
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @param mixed $available
     *
     * @return self
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     *
     * @return self
     */
    public function setCategory(array $category)
    {
        if (is_array($category)) {
            foreach ($category as $c) {
                if (is_a($c, 'WoowUpV2\Models\CategoryModel')) {
                    $this->addCategory($c);
                } elseif (is_string($c)) {
                    $this->category[] = $c;
                } else {
                    trigger_error("Category list must be an array of string or CategoryModel", E_USER_WARNING);
                }
            }
        } else {
            trigger_error("Category list must be an array of CategoryModel", 1);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpecifications()
    {
        return $this->specifications;
    }

    /**
     * @param mixed $specifications
     *
     * @return self
     */
    public function setSpecifications($specifications)
    {
        $this->specifications = $specifications;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param mixed $metadata
     *
     * @return self
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOfferPrice()
    {
        return $this->offer_price;
    }

    /**
     * @param mixed $offer_price
     *
     * @return self
     */
    public function setOfferPrice($offer_price)
    {
        $this->offer_price = $offer_price;

        return $this;
    }

    /**
     * Get custom_attributes
     * @return [type] [description]
     */
    public function getCustomAttributes()
    {
        return $this->custom_attributes;
    }

    /**
     * Set custom_attributes
     * @param array $custom_attributes [description]
     */
    public function setCustomAttributes($custom_attributes)
    {
        if (!is_array($custom_attributes) && !is_object($custom_attributes)) {
            trigger_error("Invalid custom_attributes", E_USER_WARNING);
        } else {
            foreach ($custom_attributes as $key => $value) {
                $this->addCustomAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * Add a single custom attribute
     * @param [type] $key   [description]
     * @param [type] $value [description]
     */
    public function addCustomAttribute($key, $value)
    {
        if (!empty($key)) {
            if (!isset($this->custom_attributes)) {
                $this->custom_attributes = new \stdClass();
            }
            $this->custom_attributes->{$key} = $value;
        } else {
            trigger_error("Not valid key for custom_attribute", E_USER_WARNING);
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
     * @return mixed
     */
    public function getUpdatetime()
    {
        return $this->updatetime;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function addCategory(CategoryModel $category)
    {
        $this->category[] = $category;
    }

    public function validate()
    {
        if (!isset($this->sku) || empty($this->sku)) {
            throw new \Exception("Invalid product: invalid sku", 1);
            return false;
        }
        if (!isset($this->name) || empty($this->name)) {
            throw new \Exception("Invalid product " . $this->sku . ": invalid name", 1);
            return false;
        }

        return true;
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
        $product = new self();

        foreach (json_decode($json, true) as $key => $value) {
            if (in_array($key, array_keys(get_class_vars(get_class($product))))) {
                $product->{$key} = $value;
            }
        }

        return $product;
    }
}
