<?php

namespace WoowUpV2\Models;

use WoowUpV2\Models\PurchaseItemModel as Item;
use WoowUpV2\Models\PurchasePricesModel as Prices;
use WoowUpV2\Models\PurchasePaymentModel as Payment;
use WoowUpV2\Models\SellerModel as Seller;

class PurchaseModel implements \JsonSerializable
{
    const CHANNEL_WEB = "web";
    const CHANNEL_TELEPHONE = "telephone";
    const CHANNEL_IN_STORE = "in-store";
    const CHANNEL_CORPORATE = "corporate";
    const CHANNEL_DIRECT = "direct";
    const CHANNEL_OTHER = "other";
    
    private $invoice_number;
    private $service_uid;
    private $email;
    private $document;
    private $points;
    private $channel;
    private $purchase_detail = [];
    private $prices;
    private $payment;
    private $branch_name;
    private $seller;
    private $createtime;
    private $approvedtime;
    private $metadata;

    public function __construct()
    {
        return $this;
    }

    public function validate()
    {
        if (!isset($this->invoice_number) || empty($this->invoice_number)) {
            throw new \Exception("Invalid purchase: invalid invoice_number", 1);
            return false;
        }

        if (!isset($this->purchase_detail) || empty($this->purchase_detail)) {
            throw new \Exception("Invalid purchase " . $this->invoice_number . ": invalid purchase_detail", 1);
            return false;
        }

        if (!isset($this->prices) || empty($this->prices)) {
            throw new \Exception("Invalid purchase " . $this->invoice_number . ": invalid prices", 1);
            return false;
        }

        if (!isset($this->branch_name) || empty($this->branch_name)) {
            throw new \Exception("Invalid purchase " . $this->invoice_number . ": invalid branch_name", 1);
            return false;
        }

        if (!isset($this->createtime) || empty($this->createtime)) {
            throw new \Exception("Invalid purchase " . $this->invoice_number . ": invalid createtime", 1);
            return false;
        }

        // Si está seteado payment pero es inválido devuelvo false
        if (isset($this->payment) && !$this->validatePayment($this->payment)) {
            throw new \Exception("Invalid purchase " . $this->invoice_number . ": invalid payment", 1);
            return false;
        }

        if (!isset($this->channel) || !$this->validateChannel($this->channel)) {
            throw new \Exception("Invalid purchase " . $this->invoice_number . ": invalid channel", 1);
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

        throw new \Exception("Invalid purchase " . $this->invoice_number . ": at least service_uid, document or email must be specified", 1);
        return false;
    }

    public function addItem(Item $item)
    {
        if (method_exists($item, 'validate')) {
            if ($item->validate()) {
                $this->purchase_detail[] = $item;

                return $this;
            }
        }

        throw new \Exception("Item is not valid", 1);
    }

    public function addPayment(Payment $payment)
    {
        if (!$payment->validate()) {
            throw new \Exception("Payment is not valid", 1);
        }

        if (!isset($this->payment) || empty($this->payment)) {
            $this->setPayment($payment);
        } else {
            if (!is_array($this->payment)) {
                $this->payment = array($this->payment);
            }
            $this->payment[] = $payment;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    /**
     * @param mixed $invoice_number
     *
     * @return self
     */
    public function setInvoiceNumber($invoice_number)
    {
        $this->invoice_number = $invoice_number;

        return $this;
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
    public function setServiceUid($service_uid)
    {
        $this->service_uid = $service_uid;

        $this->clearUserId();

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

        $this->clearUserId();

        return $this;
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
    public function setDocument($document)
    {
        $this->document = $document;

        $this->clearUserId();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
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
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     *
     * @return self
     */
    public function setChannel($channel)
    {
        if ($this->validateChannel($channel)) {
            $this->channel = $channel;

            return $this;
        }

        throw new \Exception("$channel is not a valid channel", 1);
    }

    /**
     * @return mixed
     */
    public function getPurchaseDetail()
    {
        return $this->purchase_detail;
    }

    /**
     * @param mixed $purchase_detail
     *
     * @return self
     */
    public function setPurchaseDetail(array $purchase_detail)
    {
        foreach ($purchase_detail as $key => $item) {
            if (!is_a($item, 'WoowUpV2\Models\PurchaseItemModel') || !$item->validate()) {
                throw new \Exception("Not valid item at index $key of purchase_detail", 1);
            }
        }

        $this->purchase_detail = $purchase_detail;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param mixed $prices
     *
     * @return self
     */
    public function setPrices(Prices $prices)
    {
        $this->prices = $prices;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param mixed $payment
     *
     * @return self
     */
    public function setPayment($payment)
    {
        if ($this->validatePayment($payment)) {
            $this->payment = $payment;

            return $this;
        }

        throw new \Exception("Payment is not valid", 1);
    }

    /**
     * @return mixed
     */
    public function getBranchName()
    {
        return $this->branch_name;
    }

    /**
     * @param mixed $branch_name
     *
     * @return self
     */
    public function setBranchName($branch_name)
    {
        $this->branch_name = $branch_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * @param mixed $seller
     *
     * @return self
     */
    public function setSeller(Seller $seller)
    {
        if ($seller->validate()) {
            $this->seller = $seller;

            return $this;
        }

        throw new \Exception("Seller is not valid", 1);
    }

    /**
     * @return mixed
     */
    public function getCreatetime()
    {
        return $this->createtime;
    }

    /**
     * @param mixed $createtime
     *
     * @return self
     */
    public function setCreatetime($createtime)
    {
        $this->createtime = $createtime;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApprovedtime()
    {
        return $this->approvedtime;
    }

    /**
     * @param mixed $approvedtime
     *
     * @return self
     */
    public function setApprovedtime($approvedtime)
    {
        $this->approvedtime = $approvedtime;

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
            if (isset($value) && !empty($value)) {
                $array[$property] = $value;
            }
        }

        return $array;
    }

    private function validateChannel($channel)
    {
        $validValues = [
            self::CHANNEL_WEB,
            self::CHANNEL_TELEPHONE,
            self::CHANNEL_IN_STORE,
            self::CHANNEL_CORPORATE,
            self::CHANNEL_DIRECT,
            self::CHANNEL_OTHER,
        ];

        if (in_array($channel, $validValues)) {
            return true;
        }

        return false;
    }

    private function validatePayment($payment)
    {
        if (!isset($payment) || empty($payment)) {
            return false;
        }

        if (is_array($payment)) {
            foreach ($payment as $p) {
                if (!is_a($p, 'WoowUpV2\Models\PurchasePaymentModel') || !$p->validate() || !$p->getTotal()) {
                    return false;
                }
            }
        } elseif (!is_a($payment, 'WoowUpV2\Models\PurchasePaymentModel') || !$payment->validate()) {
            return false;
        }

        return true;
    }

    private function clearUserId()
    {
        if (!isset($this->service_uid) || empty($this->service_uid)) {
            unset($this->service_uid);
        }

        if (!isset($this->document) || empty($this->document)) {
            unset($this->document);
        }

        if (!isset($this->email) || empty($this->email)) {
            unset($this->email);
        }
    }
}
