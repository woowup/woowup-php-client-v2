<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;

class CustomAttributeModel implements \JsonSerializable
{
    const DATA_TYPE_STRING    = 'string';
    const DATA_TYPE_INTEGER   = 'integer';
    const DATA_TYPE_TIMESTAMP = 'timestamp';
    const DATA_TYPE_FLOAT     = 'float';

    const FIELD_TYPE_TEXT     = 'text';
    const FIELD_TYPE_SELECT   = 'select';
    const FIELD_TYPE_DATETIME = 'datetime';

    private $name;
    private $data_type;
    private $label;
    private $field_type;
    private $options;

    // data-cleanser
    private $cleanser;

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
        if (!isset($this->name) || empty($this->name)) {
            return false;
        }

        if (!isset($this->data_type) || empty($this->data_type)) {
            return false;
        }

        if (!isset($this->field_type) || empty($this->field_type)) {
            return false;
        }

        if (!isset($this->label) || empty($this->label)) {
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     *
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->data_type;
    }

    /**
     * @param mixed $data_type
     *
     * @return self
     */
    public function setDataType($data_type)
    {
        if ($this->validateDataType($data_type)) {
            $this->data_type = $data_type;
        } else {
            trigger_error("Invalid data type", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFieldType()
    {
        return $this->field_type;
    }

    /**
     * @param mixed $data_type
     *
     * @return self
     */
    public function setFieldType($field_type)
    {
        if ($this->validateFieldType($field_type)) {
            $this->field_type = $field_type;
        } else {
            trigger_error("Invalid field type", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     *
     * @return self
     */
    public function setOptions($options)
    {
        if (!is_array($options)) {
            trigger_error("options must be array", E_USER_WARNING);
        }

        $this->options = $options;
        return $this;
    }

    private function validateFieldType($field_type)
    {
        $validValues = [
            self::FIELD_TYPE_TEXT,
            self::FIELD_TYPE_SELECT,
            self::FIELD_TYPE_DATETIME,
        ];

        if (in_array($field_type, $validValues)) {
            return true;
        }

        return false;
    }

    private function validateDataType($data_type)
    {
        $validValues = [
            self::DATA_TYPE_STRING,
            self::DATA_TYPE_INTEGER,
            self::DATA_TYPE_TIMESTAMP,
            self::DATA_TYPE_FLOAT,
        ];

        if (in_array($data_type, $validValues)) {
            return true;
        }

        return false;
    }

    public function jsonSerialize()
    {
        $array = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (($value !== null) && ($property !== 'cleanser')) {
                $array[$property] = $value;
            }
        }

        return $array;
    }

    public function createFromJson($json)
    {
       $customAttribute = new self();

        foreach (json_decode($json, true) as $key => $value) {
            if (isset($value) || !empty($value)) {
                switch ($key) {
                    case 'label':
                        $customAttribute->setLabel($value);
                        break;
                    case 'name':
                        $customAttribute->setName($value);
                        break;
                    case 'data_type':
                        $customAttribute->setDataType($value);
                        break;
                    case 'field_type':
                        $customAttribute->setFieldType($value);
                        break;
                    case 'options':
                        $customAttribute->setOptions($value);
                        break;
                    default:
                        $customAttribute->{$key} = $value;
                        break;
                }
            }
        }

        return $customAttribute;
    }
}