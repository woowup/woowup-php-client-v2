<?php

namespace WoowUpV2\Models;

use WoowUpV2\DataQuality\DataCleanser as DataCleanser;

/**
 * Customer of a sale for POST /purchases/with-customer. The identifiers go clean in the root and
 * what the seller typed goes in `raw`, with `email_status` / `telephone_status` saying what the
 * cleanser did with each one. The setters write the status, because they are the only ones that
 * know whether the value was kept, changed or dropped.
 *
 * Set the document, service_uid and telephone before the email: a rejected email is replaced by
 * <document|service_uid|telephone>@noemail.com, same as PurchaseModel::setEmail().
 */
class PurchaseCustomerModel implements \JsonSerializable
{
    const INVALID_EMAIL = 'noemail@noemail.com';

    const STATUS_VALIDATED = 'validated';
    const STATUS_CLEANED   = 'cleaned';
    const STATUS_GENERIC   = 'generic';
    const STATUS_REJECTED  = 'rejected';

    const EMAIL_MAX_LENGTH     = 128;
    const TELEPHONE_MAX_LENGTH = 50;

    // Filler addresses that already come like this from the source, not a typo of a real one.
    const GENERIC_EMAIL_DOMAINS = ['@noemail.com', '@mail.mercadolibre.com'];

    private $service_uid;
    private $document;
    private $email;
    private $telephone;
    private $raw = [];

    // Data cleanser
    private $cleanser;

    public function __construct()
    {
        $this->cleanser = new DataCleanser();

        return $this;
    }

    public function validate()
    {
        foreach (['service_uid', 'document', 'email', 'telephone'] as $identifier) {
            if (isset($this->{$identifier}) && $this->{$identifier} !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getServiceUid()
    {
        return $this->service_uid;
    }

    /**
     * @param string|null $service_uid
     *
     * @return self
     */
    public function setServiceUid($service_uid)
    {
        if ((is_string($service_uid) && (strlen($service_uid) > 0)) || is_null($service_uid)) {
            $this->service_uid = $service_uid;
            $this->setRaw('service_uid', $service_uid);
        } else {
            trigger_error("Invalid service_uid", E_USER_WARNING);
        }

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
     * @param string|null $document
     *
     * @return self
     */
    public function setDocument($document)
    {
        if ((is_string($document) && (strlen($document) > 0)) || is_null($document)) {
            $this->document = $document;
            $this->setRaw('document', $document);
        } else {
            trigger_error("Invalid document", E_USER_WARNING);
        }

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
     * Generic when it already comes as a filler address (@noemail.com, mercadolibre proxy); cleaned
     * when the cleanser changes anything, even case or spaces; validated when it stays the same;
     * rejected when it has no valid format, in which case <id>@noemail.com takes its place.
     *
     * Same signature as PurchaseModel::setEmail() and UserModel::setEmail(). Without sanitizing the
     * email is kept as given and gets no email_status, since the cleanser did nothing with it.
     *
     * @param string $email
     * @param bool   $sanitize
     * @param bool   $disableIdNoemail Use noemail@noemail.com instead of <id>@noemail.com
     *
     * @return self
     */
    public function setEmail(string $email, $sanitize = true, bool $disableIdNoemail = false)
    {
        if (trim($email) === '') {
            trigger_error("Invalid email", E_USER_WARNING);
            return $this;
        }

        $this->setRaw('email', $this->truncate($email, self::EMAIL_MAX_LENGTH));

        if (!$sanitize) {
            $this->email = $email;
            $this->setRaw('email_status', null);

            return $this;
        }

        if ($this->isGenericEmail($email)) {
            $this->email = $this->cleanser->email->prettify($email);
            $this->setRaw('email_status', self::STATUS_GENERIC);

            return $this;
        }

        $cleanedEmail = $this->cleanser->email->sanitize($email);

        if ($cleanedEmail === false
            || $cleanedEmail === self::INVALID_EMAIL
            || !$this->cleanser->email->validate($cleanedEmail)
            || strlen($cleanedEmail) > self::EMAIL_MAX_LENGTH
        ) {
            $localPart = $disableIdNoemail ? null : ($this->document
                ?? $this->service_uid
                ?? $this->telephone
                ?? null);

            $this->email = $localPart ? $localPart . '@noemail.com' : self::INVALID_EMAIL;
            $this->setRaw('email_status', self::STATUS_REJECTED);
            trigger_error("Email sanitization of $email failed", E_USER_WARNING);

            return $this;
        }

        $this->email = $cleanedEmail;
        $this->setRaw('email_status', $cleanedEmail === $email ? self::STATUS_VALIDATED : self::STATUS_CLEANED);

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
     * A telephone the cleanser drops is marked rejected and left out of the customer; the seller's
     * value stays in raw.
     *
     * Same signature as PurchaseModel::setTelephone() and UserModel::setTelephone(), but sanitizing
     * by default so the status is always recorded. Without sanitizing the telephone is kept as given
     * and gets no telephone_status, since the cleanser did nothing with it.
     *
     * @param string|int $telephone
     * @param bool       $sanitize
     *
     * @return self
     */
    public function setTelephone($telephone, $sanitize = true)
    {
        if (trim((string) $telephone) === '') {
            return $this;
        }

        $this->setRaw('telephone', $this->truncate((string) $telephone, self::TELEPHONE_MAX_LENGTH));

        if (!$sanitize) {
            $this->telephone = (string) $telephone;
            $this->setRaw('telephone_status', null);

            return $this;
        }

        $cleanedTelephone = $this->cleanser->telephone->sanitize($telephone);

        if ($cleanedTelephone === false) {
            $this->telephone = null;
            $this->setRaw('telephone_status', self::STATUS_REJECTED);

            return $this;
        }

        $this->telephone = $cleanedTelephone;
        $this->setRaw('telephone_status', $cleanedTelephone === (string) $telephone ? self::STATUS_VALIDATED : self::STATUS_CLEANED);

        return $this;
    }

    /**
     * @return array
     */
    public function getRaw()
    {
        return $this->raw;
    }

    public function getEmailStatus()
    {
        return $this->raw['email_status'] ?? null;
    }

    public function getTelephoneStatus()
    {
        return $this->raw['telephone_status'] ?? null;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $array = [];
        foreach (['service_uid', 'document', 'email', 'telephone'] as $property) {
            if (isset($this->{$property}) && $this->{$property} !== '') {
                $array[$property] = $this->{$property};
            }
        }

        if (!empty($this->raw)) {
            $array['raw'] = $this->raw;
        }

        return $array;
    }

    private function setRaw($key, $value)
    {
        if (is_null($value)) {
            unset($this->raw[$key]);
        } else {
            $this->raw[$key] = $value;
        }
    }

    private function isGenericEmail(string $email): bool
    {
        $email = mb_strtolower(trim($email));

        foreach (self::GENERIC_EMAIL_DOMAINS as $domain) {
            if (substr($email, -strlen($domain)) === $domain) {
                return true;
            }
        }

        return false;
    }

    private function truncate(string $value, int $length): string
    {
        return mb_substr($value, 0, $length);
    }
}
