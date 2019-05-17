<?php

namespace WoowUpV2\DataQuality;

use WoowUpV2\DataQuality\EmailCleanser as EmailCleanser;
use WoowUpV2\DataQuality\NamesCleanser as NamesCleanser;
use WoowUpV2\DataQuality\TelephoneCleanser as TelephoneCleanser;

class DataCleanser
{
	public $email;
	public $names;
	public $telephone;

	public function __construct()
	{
		$this->email     = new EmailCleanser();
		$this->names     = new NamesCleanser();
		$this->telephone = new TelephoneCleanser();
	}
}