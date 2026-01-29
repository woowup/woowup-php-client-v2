<?php

namespace WoowUpV2\DataQuality;

use WoowUpV2\DataQuality\EmailCleanser as EmailCleanser;
use WoowUpV2\DataQuality\NamesCleanser as NamesCleanser;
use WoowUpV2\DataQuality\TelephoneCleanser as TelephoneCleanser;
use WoowUpV2\DataQuality\StreetCleanser as StreetCleanser;
use WoowUpV2\DataQuality\GenderCleanser as GenderCleanser;
use WoowUpV2\DataQuality\BirthdateCleanser as BirthdateCleanser;

class DataCleanser
{
    public $email;
    public $names;
    public $telephone;
    public $street;
    public $gender;
    public $birthdate;

    public function __construct()
    {
        $this->email     = new EmailCleanser();
        $this->names     = new NamesCleanser();
        $this->telephone = new TelephoneCleanser();
        $this->street    = new StreetCleanser();
        $this->gender    = new GenderCleanser();
        $this->birthdate = new BirthdateCleanser();
    }
}