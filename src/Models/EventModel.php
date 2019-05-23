<?php

namespace WoowUpV2\Models;

class EventModel implements \JsonSerializable
{
	protected $name;

	// Non-settable properties
	protected $id;
	protected $createtime;

	public function __construct()
	{
		foreach (get_object_vars($this) as $key => $value) {
            unset($this->{$key});
        }
        
		return $this;
	}

	public function validate()
	{
		if (!isset($this->name) || !is_string($this->name) || empty($this->name)) {
			throw new \Exception("Invalid Event: invalid name", 1);
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
    public function setName(string $name)
    {
    	if (!empty($name)) {
	        $this->name = $name;

	        return $this;
	    }

	    throw new \Exception("name cannot be empty", 1);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCreatetime()
    {
        return $this->createtime;
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
    	$event = new self();

        foreach (json_decode($json) as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            switch ($key) {
                default:
                    if (isset($value) && !empty($value)) {
                        $event->{$key} = $value;
                    }
                    break;
            }
        }

        return $event;
    }

}

