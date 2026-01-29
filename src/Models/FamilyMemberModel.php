<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;

class FamilyMemberModel implements \JsonSerializable
{
    const FEMALE_GENDER_VALUE = ['f','F'];
    const MALE_GENDER_VALUE   = ['m','M'];
    const RELATIONSHIP_VALUES = [
        "son", 
        "parent", 
        "grandparent", 
        "sibling", 
        "friend", 
        "espose", 
        "grandson", 
        "nephew", 
        "pet_dog", 
        "pet_cat", 
        "pet", 
        "other"
    ];


    private $uid;
    private $email;
    private $first_name;
    private $last_name;
    private $telephone;
    private $birthdate;
    private $gender;
    private $address;
    private $relationship;

    private $id;

    // Data cleanser
    private $cleanser;

    /**
     * Constructor
     */
    public function __construct()
    {
        foreach (get_object_vars($this) as $key => $value) {
            unset($this->{$key});
        }

        $this->cleanser = new DataCleanser();

        return $this;
    }

    /**
     * Get uid
     * @return [type] [description]
     */
    public function getUid()
    {
        return isset($this->uid) ? $this->uid : null;
    }

    /**
     * Set uid
     * @param string $uid [description]
     */
    public function setUid($uid)
    {
        if ((is_string($uid) && (strlen($uid) > 0)) || is_null($uid)) {
            $this->uid = $uid;

            $this->clearUserId();
        } else {
            trigger_error("Invalid uid", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Get email
     * @return [type] [description]
     */
    public function getEmail()
    {
        return isset($this->email) ? $this->email : null;
    }

    /**
     * Set email
     * @param string $email [description]
     */
    public function setEmail(string $email, $sanitize = true)
    {
        if ($email !== '') {
            if ($sanitize) {
                if (($email = $this->cleanser->email->sanitize($email)) === false) {
                    trigger_error("Email sanitization of $email failed", E_USER_WARNING);
                    return $this;
                }
            }
            $this->email = $email;

            $this->clearUserId();
        } else {
            trigger_error("Invalid email", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Get first_name
     * @return [type] [description]
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set first_name
     * @param string $first_name [description]
     */
    public function setFirstName(string $first_name, $prettify = true)
    {
        $this->first_name = $prettify ? $this->cleanser->names->prettify($first_name) : $first_name;

        return $this;
    }

    /**
     * Get last_name
     * @return [type] [description]
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set last_name
     * @param string $last_name [description]
     */
    public function setLastName(string $last_name, $prettify = true)
    {
        $this->last_name = $prettify ? $this->cleanser->names->prettify($last_name) : $last_name;

        return $this;
    }

    /**
     * Get telephone
     * @return mixed
     */
    public function getTelephone()
    {
        return isset($this->telephone) ? $this->telephone : null;
    }

    /**
     * Set telephone
     * @param mixed $telephone
     * @return self
     */
    public function setTelephone(string $telephone, $sanitize = true)
    {
        $this->telephone = $sanitize ? $this->cleanser->telephone->sanitize($telephone) : $telephone;

        return $this;
    }

    /**
     * Get birthdate
     * @return mixed
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set birthdate
     * @param mixed $birthdate
     * @param bool $sanitize Whether to sanitize the birthdate value (default: true)
     *
     * @return self
     */
    public function setBirthdate($birthdate, $sanitize = true)
    {
        if ($sanitize) {
            $cleanedBirthdate = $this->cleanser->birthdate->sanitize($birthdate);

            if ($cleanedBirthdate === null) {
                return $this;
            }

            $this->birthdate = $cleanedBirthdate;
        } else {
            $this->birthdate = $birthdate;
        }

        return $this;
    }

    /**
     * Get gender
     * @return mixed
     */
    public function getGender()
    {
        return isset($this->gender) ? $this->gender : null;
    }

    /**
     * Set gender
     * @param mixed $gender
     *
     * @return self
     */
    public function setGender(string $gender)
    {
        if ((in_array($gender, self::FEMALE_GENDER_VALUE)) || (in_array($gender, self::MALE_GENDER_VALUE))) {
            $this->gender = $gender;
        } else {
            trigger_error("Invalid gender", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Get address
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address
     * @param mixed $address
     *
     * @return self
     */
    public function setAddress(string $address, $prettify = true)
    {
        $this->address = $prettify ? $this->cleanser->names->prettify($address) : $address;

        return $this;
    }

    /**
     * Get relationship
     * @return mixed
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * Set relationship
     * @param mixed $relationship
     *
     * @return self
     */
    public function setRelationship(string $relationship)
    {
        if (in_array($relationship, self::RELATIONSHIP_VALUES)) {
            $this->relationship = $relationship;
        } else {
            trigger_error("Invalid relationship", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->id;
    }

    /**
     * Checks if user has any valid id
     * @return boolean
     */
    public function validate()
    {
        if (isset($this->relationship) && !empty($this->relationship) && in_array($this->relationship, self::RELATIONSHIP_VALUES)) {
            return true;
        }

        throw new \Exception("Invalid member: at least relationship must be specified", 1);

        return false;
    }

    public function clearUser()
    {
        foreach (get_object_vars($this) as $property => $value) {
            if (!isset($this->{$property}) || empty($this->{$property})) {
                unset($this->{$property});
            } elseif (is_a($this->{$property}, 'stdClass')) {
                if (empty((array) $this->{$property})) {
                    unset($this->{$property});
                }
            }
        }
    }

    public function jsonSerialize()
    {
        $array = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (($value !== null)  && ($property !== 'cleanser')) {
                $array[$property] = $value;
            }
        }

        return $array;
    }

    public static function createFromJson($json)
    {
        $member = new self();

        foreach (json_decode($json, true) as $key => $value) {
            switch ($key) {
                case 'address':
                    if (isset($value)) {
                        $member->setAddress($value);
                    }
                    break;
                case 'birthday':
                    if ($value) {
                        $member->setBirthdate($value);
                    }
                    break;
                case 'relationship':
                    if (isset($value)) {
                        $member->setRelationship($value);
                    }
                    break;
                default:
                    if (in_array($key, array_keys(get_class_vars(get_class($member)))) && (isset($value))) {
                        $member->{$key} = $value;
                    } else {
                        if (!in_array($key, array_keys(get_class_vars(get_class($member))))) {
                            trigger_error("$key not valid", E_USER_WARNING);
                        }
                    }
                    break;
            }
        }

        return $member;
    }

    private function clearUserId()
    {
        if (!isset($this->document) || ($this->document === '')) {
            unset($this->document);
        }

        if (!isset($this->email) || empty($this->email)) {
            unset($this->email);
        }
    }
}
