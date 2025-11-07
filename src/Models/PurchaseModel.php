<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;
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
    private $telephone;
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
    private $custom_attributes;

    // Data cleanser
    private $cleanser;

    public function __construct()
    {
        $this->cleanser = new DataCleanser();

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

        if (isset($this->telephone) && !empty($this->telephone)) {
            return true;
        }

        throw new \Exception("Invalid purchase " . $this->invoice_number . ": at least service_uid, document, telephone or email must be specified", 1);
        return false;
    }

    public function addItem(Item $item)
    {
        if (method_exists($item, 'validate')) {
            if ($item->validate()) {
                $this->purchase_detail[] = $item;
            }
        } else {
            trigger_error("Invalid item", E_USER_WARNING);
        }

        return $this;
    }

    public function addPayment(Payment $payment)
    {
        if (!$payment->validate()) {
            trigger_error("Invalid payment", E_USER_WARNING);
        } else {
            if (!isset($this->payment) || empty($this->payment)) {
                $this->setPayment($payment);
            } else {
                if (!is_array($this->payment)) {
                    $this->payment = array($this->payment);
                }
                $this->payment[] = $payment;
            }
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
        return isset($this->service_uid) ? $this->service_uid : null;
    }

    /**
     * @param mixed $service_uid
     *
     * @return self
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
     * @return mixed
     */
    public function getEmail()
    {
        return isset($this->email) ? $this->email : null;
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
                    trigger_error("Email sanitization of $email failed", E_USER_WARNING);
                    $email = null;
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
     * @return mixed
     */
    public function getDocument()
    {
        return isset($this->document) ? $this->document : null;
    }

    /**
     * @param mixed $document
     *
     * @return self
     */
    public function setDocument($document)
    {
        if ((is_string($document) && (strlen($document) > 0)) || is_null($document)) {
            $this->document = $document;

            $this->clearUserId();
        } else {
            trigger_error("Invalid document", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return isset($this->telephone) ? $this->telephone : null;
    }

    /**
     * Set telephone
     *
     * @param mixed $telephone The telephone to set
     * @param bool $sanitize Whether to sanitize the input
     *
     * @return self
     */
    public function setTelephone($telephone, $sanitize = false)
    {
        if (trim($telephone) === '') {
            return $this;
        }

        if ($sanitize) {
            $telephone = $this->cleanser->telephone->sanitize($telephone);
            if ($telephone === false) {
                return $this;
            }
        }

        $this->telephone = $telephone;
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
        } else {
            trigger_error("Invalid channel", E_USER_WARNING);
        }

        return $this;
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
                trigger_error("Not valid item at index $key of purchase_detail", E_USER_WARNING);
                return $this;
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
        } else {
            trigger_error("Invalid payment", E_USER_WARNING);
        }

        return $this;
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
    public function setBranchName($branch_name, $prettify = true)
    {
        $this->branch_name = $prettify ? $this->cleanser->names->prettify($branch_name) : $branch_name;

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
        } else {
            trigger_error("Invalid seller", E_USER_WARNING);
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
            trigger_error("custom_attributes must be array or object", E_USER_WARNING);
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

    public function countUnits()
    {
        $count = 0;
        if (isset($this->purchase_detail) && count($this->purchase_detail)) {
            foreach ($this->purchase_detail as $item) {
                $count += $item->getQuantity();
            }
        }

        return $count;
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
        $purchase = new self();

        foreach (get_object_vars($purchase) as $property => $value) {
            if (!isset($purchase->{$property})) {
                unset($purchase->{$property});
            }
        }

        foreach (json_decode($json) as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            switch ($key) {
                case 'purchase_detail':
                    $items = (isset($value->items) ? $value->items : $value);
                    foreach ($items as $purchaseItem) {
                        $item = Item::createFromJson(json_encode($purchaseItem));
                        $purchase->addItem($item);
                    }
                    break;
                case 'prices':
                    $prices = Prices::createFromJson(json_encode($value));
                    $purchase->setPrices($prices);
                    break;
                case 'downloadtime':
                    $purchase->setApprovedtime($value);
                    break;
                case 'branch':
                    $purchase->setBranchName($value->name);
                    break;
                case 'payment':
                    if (is_array($value)) {
                        foreach ($value as $purchasePayment) {
                            $payment = Payment::createFromJson(json_encode($purchasePayment));
                            $purchase->addPayment($payment);
                        }
                    } else {
                        $payment = Payment::createFromJson(json_encode($value));
                        $purchase->addPayment($payment);
                    }
                    break;
                case 'seller':
                case 'purchase_operator':
                    $seller = Seller::createFromJson(json_encode($value));
                    $purchase->setSeller($seller);
                    break;
                default:
                    if (isset($value) && !empty($value)) {
                        $purchase->{$key} = $value;
                    }
                    break;
            }
        }

        return $purchase;
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

        if (!isset($this->telephone) || empty($this->telephone)) {
            unset($this->telephone);
        }
    }
}
