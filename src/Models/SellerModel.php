<?php

namespace WoowUp\Models;

/**
 *
 */
class SellerModel implements \JsonSerializable
{
    private $name;
    private $email;
    private $external_id;
    
    public function __construct()
    {
        return $this;
    }

    public function validate()
    {
        if (!isset($this->name) || empty($this->name)) {
            return false;
        }

        if (!isset($this->email) || empty($this->email)) {
            return false;
        }

        return true;
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
    public function setName($name)
    {
        $this->name = $name;

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
    public function setEmail($email)
    {
        $this->email = $email;

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
    public function setExternalId($external_id)
    {
        $this->external_id = $external_id;

        return $this;
    }

    public function jsonSerialize()
    {
        $array = [];
        foreach (get_object_vars($this) as $property => $value) {
            if ($value !== null) {
                $array[$property] = $value;
            }
        }

        return $array;
    }
}
