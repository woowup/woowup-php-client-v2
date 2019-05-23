<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;

class UserEventModel implements \JsonSerializable
{
	protected $event;
	protected $service_uid;
	protected $email;
	protected $document;
	protected $datetime;
	protected $metadata;

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

	public function validate()
	{
		if (!isset($this->event) || !is_string($this->event) || empty($this->event)) {
			throw new \Exception("event cannot be empty", 1);
			return false;
		}

		if (!isset($this->datetime) || !is_string($this->datetime) || empty($this->datetime)) {
			throw new \Exception("datetime cannot be empty", 1);
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

        throw new \Exception("Invalid User Event : at least service_uid, document or email must be specified", 1);
        return false;
	}

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     *
     * @return self
     */
    public function setEvent(string $event)
    {
    	if (!empty($event)) {
	        $this->event = $event;

	        return $this;
	    }

	    throw new \Exception("event cannot be empty", 1);
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

            return $this;
        }

        throw new \Exception("service_uid can be null or string with at least 1 character long", 1);
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
                    throw new \Exception("Email sanitization failed", 1);
                }
            }
            $this->email = $email;

            return $this;
        }

        throw new \Exception("email cannot be empty", 1);
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

            return $this;
        }

        throw new \Exception("document cannot be empty", 1);
    }

    /**
     * @return mixed
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param mixed $datetime
     *
     * @return self
     */
    public function setDatetime(string $datetime)
    {
        $this->datetime = $datetime;

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

    public function jsonSerialize()
    {
    	$array = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (isset($value)) {
                $array[$property] = $value;
            }
        }

        return $array;
    }

    public static function createFromJson($json)
    {
    	$userEvent = new self();

        foreach (json_decode($json) as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            switch ($key) {
                default:
                    if (isset($value) && !empty($value)) {
                        $userEvent->{$key} = $value;
                    }
                    break;
            }
        }

        return $userEvent;
    }
}