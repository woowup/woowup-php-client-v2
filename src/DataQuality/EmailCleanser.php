<?php

namespace WoowUpV2\DataQuality;

use Mailcheck\Mailcheck as Mailcheck;

class EmailCleanser
{
	const GENERIC_TLDS    = ["com", "net", "org", "info", "edu", "gov", "mil"];
	const GEOGRAPHIC_TLDS = ["ar", "es", "co", "pe", "bo", "br", "fr", "do", "co.uk"];

	public function __construct()
	{
		return $this;
	}

	public function sanitize($email)
	{
		$mailcheck = new Mailcheck();
		$mailcheck->setPopularTlds($this->buildPopularTlds());

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

	protected function buildPopularTlds()
	{
		$popularTlds = array_merge(self::GENERIC_TLDS, self::GEOGRAPHIC_TLDS);
		foreach (self::GENERIC_TLDS as $genericTld) {
			foreach (self::GEOGRAPHIC_TLDS as $geoTld) {
				$popularTlds[] = $genericTld . "." . $geoTld;
			}
		}

		return $popularTlds;
	}
}