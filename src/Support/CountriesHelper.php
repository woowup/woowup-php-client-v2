<?php

namespace WoowUpV2\Support;

class CountriesHelper
{
	const MAX_LEVENSHTEIN = 3;

	protected $countries;
	
	public function __construct()
	{
		$this->countries = json_decode(file_get_contents(__DIR__ . '/countries.json'));
	}

	public function getISO3CodeByCountryName($search)
	{
		$minDiff = 999;
		foreach ($this->countries as $country) {
			$levDiff = levenshtein(mb_strtolower($this->removeAccents($country->Country)), mb_strtolower($this->removeAccents($search)));
			if ($levDiff === 0) {
				return $country->{'ISO-3'};
			} elseif (($levDiff < $minDiff) && ($levDiff <= self::MAX_LEVENSHTEIN)) {
				$minDiff = $levDiff;
				$candidate = $country->{'ISO-3'};
			}
		}

		return isset($candidate) ? $candidate : null;
	}

	public function ISO2ToISO3($code)
	{
		$code = strtoupper($code);
		foreach ($this->countries as $country) {
			if ($country->{'ISO-2'} === $code) {
				return $country->{'ISO-3'};
			}
		}

		return null;
	}

	private function removeAccents($string)
	{
		$accents = '/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/';

		$string_encoded = htmlentities($string,ENT_NOQUOTES,'UTF-8');

		return preg_replace($accents,'$1',$string_encoded);
	}
}