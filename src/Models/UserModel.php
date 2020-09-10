<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;
use WoowUpV2\Support\CountriesHelper as CountriesHelper;

class UserModel implements \JsonSerializable
{
    const FEMALE_GENDER_VALUE = 'F';
    const MALE_GENDER_VALUE = 'M';

    const MARITAL_STATUS_VALUES = [
        'single',
        'commited',
        'married',
        'divorced',
        'widowed',
    ];

    const ENABLED_VALUE = "enabled";
    const DISABLED_VALUE = "disabled";

    const DISABLED_REASON_VALUES = [
        'bounce',
        'unsuscribe',
        'spamreport',
        'dropped',
        'other',
    ];

    const CAN_BE_NULL_FIELDS = [
        'service_uid',
        'birthdate',
    ];

    private $service_uid;
    private $document;
    private $email;
    private $new_email;
    private $first_name;
    private $last_name;
    private $telephone;
    private $birthdate;
    private $gender;
    private $street;
    private $postcode;
    private $city;
    private $department;
    private $state;
    private $country;
    private $document_type;
    private $marital_status;
    private $tags;
    private $points;
    private $mailing_enabled;
    private $sms_enabled;
    private $mailing_disabled_reason;
    private $sms_disabled_reason;
    private $custom_attributes;
    private $club_inscription_date;

    // Not set-able properties (not update-able via API)
    private $blocked;
    private $customform;
    private $notes;
    private $family;
    private $createtime;
    private $updatetime;
    private $sms_enabled_type;
    private $sms_enabled_updatetime;
    private $mailing_enabled_type;
    private $mailing_enabled_updatetime;
    private $userapp_id;
    private $user_id;
    private $app_id;

    // Data cleanser
    private $cleanser;

    // Countries
    private $countriesHelper;

    /**
     * Constructor
     */
    public function __construct()
    {
        foreach (get_object_vars($this) as $key => $value) {
            unset($this->{$key});
        }

        $this->cleanser        = new DataCleanser();
        $this->countriesHelper = new CountriesHelper();

        return $this;
    }

    /**
     * Get service_uid
     * @return [type] [description]
     */
    public function getServiceUid()
    {
        return isset($this->service_uid) ? $this->service_uid : null;
    }

    /**
     * Set service_uid
     * @param string $service_uid [description]
     */
    public function setServiceUid($service_uid)
    {
        if ((is_string($service_uid) && (strlen($service_uid) > 0)) || is_null($service_uid)) {
            $this->service_uid = $service_uid;

            $this->clearUserId();
        } else {
            trigger_error("Invalid service_uid", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Get document
     * @return [type] [description]
     */
    public function getDocument()
    {
        return isset($this->document) ? $this->document : null;
    }

    /**
     * Set document
     * @param string $document [description]
     */
    public function setDocument(string $document)
    {
        if ($document !== '') {
            $this->document = $document;

            $this->clearUserId();
        } else {
            trigger_error("Invalid document", E_USER_WARNING);
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
     * Set no contact email
     * @param string $new_email [description]
     */
    public function setNewEmail(string $email, $sanitize = true)
    {
        if ($email !== '') {
            if ($sanitize) {
                if (($email = $this->cleanser->email->sanitize($email)) === false) {
                    trigger_error("Email sanitization of $email failed", E_USER_WARNING);
                    return $this;
                }
            }
            $this->new_email = $email;
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

    public function setFullNameAndGender($fullName)
    {
        list($firstName, $lastName, $gender) = $this->cleanser->names->splitAndBuildNames($fullName);

        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setGender($gender);

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
     *
     * @return self
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
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
        if (($gender === self::FEMALE_GENDER_VALUE) || ($gender === self::MALE_GENDER_VALUE)) {
            $this->gender = $gender;
        } else {
            trigger_error("Invalid gender", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Get street
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street
     * @param mixed $street
     *
     * @return self
     */
    public function setStreet(string $street, $prettify = true)
    {
        $this->street = $prettify ? $this->cleanser->names->prettify($street) : $street;

        return $this;
    }

    /**
     * Get postcode
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set postcode
     * @param mixed $postcode
     *
     * @return self
     */
    public function setPostcode(string $postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get city
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set city
     * @param mixed $city
     *
     * @return self
     */
    public function setCity(string $city, $prettify = true)
    {
        $this->city = $prettify ? $this->cleanser->names->prettify($city) : $city;

        return $this;
    }

    /**
     * Get department
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set department
     * @param mixed $department
     *
     * @return self
     */
    public function setDepartment(string $department, $prettify = true)
    {
        $this->department = $prettify ? $this->cleanser->names->prettify($department) : $department;

        return $this;
    }

    /**
     * Get state
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state
     * @param mixed $state
     *
     * @return self
     */
    public function setState(string $state, $prettify = true)
    {
        $this->state = $prettify ? $this->cleanser->names->prettify($state) : $state;

        return $this;
    }

    /**
     * Get country
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set country
     * @param mixed $country
     *
     * @return self
     */
    public function setCountry(string $country)
    {
        if (strlen($country) === 3) {
            $code = strtoupper($country);
        } elseif (strlen($country) === 2) {
            $code = $this->countriesHelper->ISO2ToISO3($country);
        } else {
            $code = $this->countriesHelper->getISO3CodeByCountryName($country);
        }

        if ($code) {
            $this->country = $code;
        }

        return $this;
    }

    /**
     * Get document_type
     * @return mixed
     */
    public function getDocumentType()
    {
        return $this->document_type;
    }

    /**
     * Set document_type
     * @param mixed $document_type
     *
     * @return self
     */
    public function setDocumentType(string $document_type, $prettify = true)
    {
        $this->document_type = $prettify ? $this->cleanser->names->prettify($document_type) : $prettify;

        return $this;
    }

    /**
     * Get marital_status
     * @return mixed
     */
    public function getMaritalStatus()
    {
        return $this->marital_status;
    }

    /**
     * Set marital_status
     * @param mixed $marital_status
     *
     * @return self
     */
    public function setMaritalStatus(string $marital_status)
    {
        if (in_array($marital_status, self::MARITAL_STATUS_VALUES)) {
            $this->marital_status = $marital_status;
        } else {
            trigger_error("Invalid marital_status", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Get tags
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set tags
     * @param mixed $tags
     *
     * @return self
     */
    public function setTags(string $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get points
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set points
     * @param mixed $points
     *
     * @return self
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get mailing_enabled
     * @return mixed
     */
    public function getMailingEnabled()
    {
        return $this->mailing_enabled;
    }

    /**
     * Set mailing_enabled
     * @param mixed $mailing_enabled
     *
     * @return self
     */
    public function setMailingEnabled($mailing_enabled)
    {
        if (is_bool($mailing_enabled)) {
            $this->mailing_enabled = $mailing_enabled ? self::ENABLED_VALUE : self::DISABLED_VALUE;
        } elseif (($mailing_enabled === self::ENABLED_VALUE) || ($mailing_enabled === self::DISABLED_VALUE)) {
            $this->mailing_enabled = $mailing_enabled;
        } else {
            trigger_error("Invalid mailing_enabled", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Get sms_enabled
     * @return mixed
     */
    public function getSmsEnabled()
    {
        return $this->sms_enabled;
    }

    /**
     * Set sms_enabled
     * @param mixed $sms_enabled
     *
     * @return self
     */
    public function setSmsEnabled($sms_enabled)
    {
        if (is_bool($sms_enabled)) {
            $this->sms_enabled = $sms_enabled ? self::ENABLED_VALUE : self::DISABLED_VALUE;
        } elseif (($sms_enabled === self::ENABLED_VALUE) || ($sms_enabled === self::DISABLED_VALUE)) {
            $this->sms_enabled = $sms_enabled;
        } else {
            trigger_error("Invalid sms_enabled", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Get mailing_disabled_reason
     * @return mixed
     */
    public function getMailingDisabledReason()
    {
        return $this->mailing_disabled_reason;
    }

    /**
     * Set mailing_disabled_reason
     * @param mixed $mailing_disabled_reason
     *
     * @return self
     */
    public function setMailingDisabledReason($mailing_disabled_reason)
    {
        if (in_array($mailing_disabled_reason, self::DISABLED_REASON_VALUES)) {
            $this->mailing_disabled_reason = $mailing_disabled_reason;
        } else {
            trigger_error("Invalid mailing_disabled_reason", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Get sms_disabled_reason
     * @return mixed
     */
    public function getSmsDisabledReason()
    {
        return $this->sms_disabled_reason;
    }

    /**
     * Set sms_disabled_reason
     * @param mixed $sms_disabled_reason
     *
     * @return self
     */
    public function setSmsDisabledReason($sms_disabled_reason)
    {
        if (in_array($sms_disabled_reason, self::DISABLED_REASON_VALUES)) {
            $this->sms_disabled_reason = $sms_disabled_reason;
        } else {
            trigger_error("Invalid sms_disabled_reason", E_USER_WARNING);
        }

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
            trigger_error("Invalid key for custom_attribute", E_USER_WARNING);
        }

        return $this;
    }

        /**
     * @return mixed
     */
    public function getUserappId()
    {
        return $this->userapp_id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->app_id;
    }

    /**
     * @return mixed
     */
    public function getMailingEnabledType()
    {
        return $this->mailing_enabled_type;
    }

    /**
     * @return mixed
     */
    public function getMailingEnabledUpdatetime()
    {
        return $this->mailing_enabled_updatetime;
    }

    /**
     * @return mixed
     */
    public function getSmsEnabledType()
    {
        return $this->sms_enabled_type;
    }

    /**
     * @return mixed
     */
    public function getSmsEnabledUpdatetime()
    {
        return $this->sms_enabled_updatetime;
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

    public function setClubInscriptionDate($club_inscription_date)
    {
        $this->club_inscription_date = $club_inscription_date;

        return $this;
    }

    public function getClubInscriptionDate()
    {
        return $this->club_inscription_date;
    }

    public function setGenderizeApikey($genderize_apikey)
    {
        $this->genderize_apikey = $genderize_apikey;

        return $this;
    }

    /**
     * Checks if user has any valid id
     * @return boolean
     */
    public function validate()
    {
        if (isset($this->service_uid) && !empty($this->service_uid)) {
            return true;
        }
        if (isset($this->document) && !empty($this->document)) {
            return true;
        }
        if (isset($this->email) && !empty($this->email)) {
            return true;
        }
        if (isset($this->telephone) && !empty($this->telephone)) {
            return true;
        }

        throw new \Exception("Invalid user: at least service_uid, document, telephone or email must be specified", 1);

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
            if ((($value !== null) || in_array($property, self::CAN_BE_NULL_FIELDS)) && ($property !== 'cleanser') && ($property !== 'countriesHelper')) {
                $array[$property] = $value;
            }
        }

        return $array;
    }

    public static function createFromJson($json)
    {
        $user = new self();

        foreach (json_decode($json, true) as $key => $value) {
            switch ($key) {
                case 'address':
                    if (isset($value)) {
                        $user->setStreet($value);
                    }
                    break;
                case 'birthday':
                    if ($value) {
                        $user->setBirthdate($value);
                    }
                    break;
                case 'postal_code':
                    if (isset($value)) {
                        $user->setPostcode($value);
                    }
                    break;
                case 'sms_enabled_reason':
                    if (isset($value)) {
                        $user->setSmsDisabledReason($value);
                    }
                    break;
                case 'mailing_enabled_reason':
                    if (isset($value)) {
                        $user->setMailingDisabledReason($value);
                    }
                    break;
                case 'custom_attributes':
                    if (isset($value) && !empty($value)) {
                        $user->setCustomAttributes($value);
                    }
                    break;
                case 'customform':
                    if (isset($value) && !empty($value)) {
                        $user->customform = $value;
                    }
                    break;
                case 'mailing_enabled':
                    $user->setMailingEnabled($value);
                    break;
                case 'sms_enabled':
                    $user->setSmsEnabled($value);
                    break;
                default:
                    if (in_array($key, array_keys(get_class_vars(get_class($user)))) && (isset($value) || in_array($key, self::CAN_BE_NULL_FIELDS))) {
                        $user->{$key} = $value;
                    } else {
                        if (!in_array($key, array_keys(get_class_vars(get_class($user))))) {
                            trigger_error("$key not valid", E_USER_WARNING);
                        }
                    }
                    break;
            }
        }

        return $user;
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
