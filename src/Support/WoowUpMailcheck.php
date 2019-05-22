<?php

namespace WoowUpV2\Support;

use Mailcheck\Mailcheck as Mailcheck;

class WoowUpMailcheck extends Mailcheck
{
	const GENERIC_TLDS    = ["com", "net", "org", "info", "edu", "gov", "mil"];
	const GEOGRAPHIC_TLDS = ["ar", "es", "co", "pe", "bo", "br", "fr", "do", "co.uk"];


	public function __construct()
	{
		$this->setPopularTlds($this->buildPopularTlds());

		return $this;
	}

	public function buildPopularTlds()
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