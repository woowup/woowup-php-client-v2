<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;

/**
 *
 */
class PurchasePaymentModel implements \JsonSerializable
{
    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';
    const TYPE_OTHER = 'other';
    const TYPE_MP = 'mercadopago';
    const TYPE_TP = 'todopago';
    const TYPE_CASH = 'cash';

    private $type;
    private $brand;
    private $bank;
    private $total;
    private $installments;

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
        if (!isset($this->type) || empty($this->type) || !$this->validateType($this->type)) {
            throw new \Exception("Invalid payment: invalid type", 1);
            
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return self
     */
    public function setType($type)
    {
        if ($this->validateType($type)) {
            $this->type = $type;
        }

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
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param mixed $bank
     *
     * @return self
     */
    public function setBank($bank, $prettify = true)
    {
        $this->bank = $prettify ? $this->cleanser->names->prettify($bank) : $bank;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     *
     * @return self
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @param mixed $installments
     *
     * @return self
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;

        return $this;
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
        $payment = new self();

        foreach (json_decode($json, true) as $key => $value) {
            switch ($key) {
                case 'total':
                    $payment->setTotal((float) $value);
                    break;
                case 'installments':
                    $payment->setInstallments((int) $value);
                    break;
                case 'name':
                    $payment->setBank($value);
                    break;
                default:
                    if (in_array($key, array_keys(get_class_vars(get_class($payment))))) {
                        $payment->{$key} = $value;
                    }
                    break;
            }
        }

        return $payment;
    }

    private function validateType($type)
    {
        $validValues = [
            self::TYPE_CREDIT,
            self::TYPE_DEBIT,
            self::TYPE_OTHER,
            self::TYPE_MP,
            self::TYPE_TP,
            self::TYPE_CASH,
        ];

        if (in_array($type, $validValues)) {
            return true;
        }

        return false;
    }
}
