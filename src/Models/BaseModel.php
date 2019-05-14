<?php

namespace WoowUp\Models;

class BaseModel
{
	/**
	 * Capitalizes every word of a string. E.g. "hEllO WORLD!" -> "Hello World!" 
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	protected function prettifyString($string)
	{
		return ucwords(mb_strtolower($string));
	}

	/**
	 * Sanitizes email
	 * @param  [type] $email [description]
	 * @return [type]        [description]
	 */
	public function sanitizeEmail($email)
	{
		$mailcheck = new \Mailcheck\Mailcheck();
		$email = $mailcheck->suggest(mb_strtolower($email));
		if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
			return $email;
		} else {
			$email = filter_var($email, FILTER_SANITIZE_EMAIL);
			if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
				return $email;
			}
		}

		return false;
	}

	protected function normalizeTelephone($telephone)
	{
		// TO-DO
		return $telephone;
	}

	protected function normalizeAddress($address)
	{
		// TO-DO
		return $address;
	}

	protected function normalizeCity($city)
	{
		// TO-DO
		return $city;
	}

	protected function normalizeState($state)
	{
		// TO-DO
		return $state;
	}

	protected function normalizeCountry($country)
	{
		// TO-DO
		return $country;
	}


}