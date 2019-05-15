<?php

namespace WoowUp\DataQuality;

use WoowUp\DataQuality\EmailCleanser as EmailCleanser;
use WoowUp\DataQuality\NamesCleanser as NamesCleanser;
use WoowUp\DataQuality\TelephoneCleanser as TelephoneCleanser;

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