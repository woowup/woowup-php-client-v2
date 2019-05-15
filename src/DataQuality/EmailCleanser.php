<?php

namespace WoowUp\DataQuality;

use Mailcheck\Mailcheck as Mailcheck;

class EmailCleanser
{
	public function __construct()
	{
		return $this;
	}

	public function sanitize($email)
	{
		$mailcheck = new Mailcheck();
		$email = self::prettify($email);
		$email = $mailcheck->suggest($email);
		if (self::validate($email)) {
			return $email;
		} else {
			$email = filter_var($email, FILTER_SANITIZE_EMAIL);
			if (self::validate($email)) {
				return $email;
			}
		}

		return false;
	}

	public function validate($email)
	{
		return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
	}

	public function prettify($email)
	{
		return utf8_encode(mb_strtolower(trim($email)));
	}
}