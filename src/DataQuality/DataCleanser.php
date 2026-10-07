<?php

namespace WoowUpV2\DataQuality;

use WoowUpV2\DataQuality\EmailCleanser as EmailCleanser;
use WoowUpV2\DataQuality\Tld\TldCorrector;
use WoowUpV2\DataQuality\NamesCleanser as NamesCleanser;
use WoowUpV2\DataQuality\TelephoneCleanser as TelephoneCleanser;
use WoowUpV2\DataQuality\StreetCleanser as StreetCleanser;
use WoowUpV2\DataQuality\PostcodeCleanser as PostcodeCleanser;
use WoowUpV2\DataQuality\GenderCleanser as GenderCleanser;
use WoowUpV2\DataQuality\BirthdateCleanser as BirthdateCleanser;
use WoowUpV2\DataQuality\CustomAttributeCleanser as CustomAttributeCleanser;

class DataCleanser
{
    public $email;
    public $names;
    public $telephone;
    public $street;
    public $postcode;
    public $gender;
    public $birthdate;
    public $customAttributes;

    private static ?TldCorrector $globalTldCorrector = null;

    /**
     * Configure a TldCorrector that will be used by all DataCleanser instances created afterwards.
     * Call once at application startup (e.g. from a feature-flag check in the Pimple provider).
     * Pass null to clear it — required by callers that process several accounts in the same
     * process without forking (e.g. a command run with no parallelism), so a corrector configured
     * for one account doesn't leak into the next one that has the feature flag off.
     */
    public static function configureGlobalTldCorrector(?TldCorrector $corrector = null): void
    {
        self::$globalTldCorrector = $corrector;
    }

    /**
     * Lets a caller outside the DataCleanser/EmailCleanser chain build its own EmailCleanser with
     * whatever corrector is configured right now, instead of a full DataCleanser just to reach
     * ->email, or a cached instance that keeps an old corrector after the next account clears it.
     */
    public static function getGlobalTldCorrector(): ?TldCorrector
    {
        return self::$globalTldCorrector;
    }

    public function setTldCorrector(TldCorrector $corrector): void
    {
        $this->email = new EmailCleanser($corrector);
    }

    public function __construct()
    {
        $this->email            = new EmailCleanser(self::$globalTldCorrector);
        $this->names            = new NamesCleanser();
        $this->telephone        = new TelephoneCleanser();
        $this->street           = new StreetCleanser();
        $this->postcode         = new PostcodeCleanser();
        $this->gender           = new GenderCleanser();
        $this->birthdate        = new BirthdateCleanser();
        $this->customAttributes = new CustomAttributeCleanser();
    }
}