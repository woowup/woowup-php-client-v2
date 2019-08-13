<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;

class BranchModel implements \JsonSerializable
{
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    const BUSINESS_TYPE_VALUES = [
        'own',
        'franchisee',
        null,
    ];

    const FORMAT_VALUES = [
        'brand_branch',
        'multibrand_branch',
        'brand_island',
        'multibrand_island',
        'outlet',
        null,
    ];

    private $id;
    private $name;
    private $description;
    private $display_name;
    private $email;
    private $telephone;
    private $address;
    private $working_hours;
    private $notes;
    private $branch_zone;
    private $holder;
    private $status;
    private $country_code;
    private $state;
    private $city;
    private $business_type;
    private $shopping_center;
    private $location_type;
    private $m2;
    private $m2_cost;
    private $employees_quantity;
    private $group;
    private $format;
    private $is_web;

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

    public function getId()
    {
        return $this->id;
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
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * @param mixed $display_name
     *
     * @return self
     */
    public function setDisplayName($display_name, $prettify = true)
    {
        $this->display_name = $prettify ? $this->cleanser->names->prettify($display_name) : $display_name;

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
    public function setEmail($email, $sanitize = true)
    {
        if ($email !== '') {
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
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param mixed $telephone
     *
     * @return self
     */
    public function setTelephone($telephone, $sanitize = true)
    {
        $this->telephone = $sanitize ? $this->cleanser->telephone->sanitize($telephone) : $telephone;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     *
     * @return self
     */
    public function setAddress($address, $prettify = true)
    {
        $this->address = $prettify ? $this->cleanser->names->prettify($address) : $address;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWorkingHours()
    {
        return $this->working_hours;
    }

    /**
     * @param mixed $working_hours
     *
     * @return self
     */
    public function setWorkingHours($working_hours)
    {
        $this->working_hours = $working_hours;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     *
     * @return self
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBranchZone()
    {
        return $this->branch_zone;
    }

    /**
     * @param mixed $branch_zone
     *
     * @return self
     */
    public function setBranchZone($branch_zone, $prettify = true)
    {
        if (is_array($branch_zone)) {
            $branch_zone = (object) $branch_zone;
        }

        if (isset($branch_zone->code)) {
            if (isset($branch_zone->name) && $prettify) {
                $branch_zone->name = $this->cleanser->names->prettify($branch_zone->name);
            }
            $this->branch_zone = $branch_zone;
        } else {
            trigger_error("Invalid branch_zone", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * @param mixed $holder
     *
     * @return self
     */
    public function setHolder($holder, $prettify = true)
    {
        $this->holder = $prettify ? $this->cleanser->names->prettify($holder) : $holder;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return self
     */
    public function setStatus($status)
    {
        if (($status === self::STATUS_ACTIVE) || ($status === self::STATUS_INACTIVE)) {
            $this->status = $status;
        } else {
            trigger_error("Invalid status", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @param mixed $country_code
     *
     * @return self
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     *
     * @return self
     */
    public function setState($state, $prettify = true)
    {
        $this->state = $prettify ? $this->cleanser->names->prettify($state) : $state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     *
     * @return self
     */
    public function setCity($city, $prettify = true)
    {
        $this->city = $prettify ? $this->cleanser->names->prettify($city) : $city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusinessType()
    {
        return $this->business_type;
    }

    /**
     * @param mixed $business_type
     *
     * @return self
     */
    public function setBusinessType($business_type)
    {
        if (in_array($business_type, self::BUSINESS_TYPE_VALUES)) {
            $this->business_type = $business_type;
        } else {
            trigger_error("Invalid business_type", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShoppingCenter()
    {
        return $this->shopping_center;
    }

    /**
     * @param mixed $shopping_center
     *
     * @return self
     */
    public function setShoppingCenter($shopping_center, $prettify = true)
    {
        $this->shopping_center = $prettify ? $this->cleanser->names->prettify($shopping_center) : $shopping_center;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocationType()
    {
        return $this->location_type;
    }

    /**
     * @param mixed $location_type
     *
     * @return self
     */
    public function setLocationType($location_type, $prettify = true)
    {
        $this->location_type = $prettify ? $this->cleanser->names->prettify($location_type) : $location_type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getM2()
    {
        return $this->m2;
    }

    /**
     * @param mixed $m2
     *
     * @return self
     */
    public function setM2($m2)
    {
        $this->m2 = $m2;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getM2Cost()
    {
        return $this->m2_cost;
    }

    /**
     * @param mixed $m2_cost
     *
     * @return self
     */
    public function setM2Cost($m2_cost)
    {
        $this->m2_cost = $m2_cost;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmployeesQuantity()
    {
        return $this->employees_quantity;
    }

    /**
     * @param mixed $employees_quantity
     *
     * @return self
     */
    public function setEmployeesQuantity($employees_quantity)
    {
        $this->employees_quantity = $employees_quantity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     *
     * @return self
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     *
     * @return self
     */
    public function setFormat($format)
    {
        if (in_array($format, self::FORMAT_VALUES)) {
            $this->format = $format;
        } else {
            trigger_error("Invalid format", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsWeb()
    {
        return $this->is_web;
    }

    /**
     * @param mixed $is_web
     *
     * @return self
     */
    public function setIsWeb($is_web)
    {
        $this->is_web = $is_web;

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
        $branch = new self();

        foreach (json_decode($json, true) as $key => $value) {
            $branch->{$key} = $value;
        }

        return $branch;
    }

    public function validate()
    {
        if (!isset($this->name) || empty($this->name)) {
            throw new \Exception("Invalid name", 1);
        }

        return true;
    }
}
