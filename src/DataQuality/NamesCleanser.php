<?php

namespace WoowUpV2\DataQuality;

use WoowUpV2\Support\Genderizer as Genderizer;

class NamesCleanser
{
	const FIRST_NAME_INDEX = 0;
	const LAST_NAME_INDEX  = 1;
	private $genderizer;

	public function __construct($genderizeApikey = null)
	{
		$this->genderizer = new Genderizer($genderizeApikey);
		return $this;
	}

	public function splitAndBuildNames($fullname, $delimiter = ' ')
	{
		$splittedNames = [
			self::FIRST_NAME_INDEX => [],
			self::LAST_NAME_INDEX  => [],
		];

		$parts = explode($delimiter, $this->prettify($fullname));

		if (count($parts) < 1) {
			throw new \Exception("Invalid delimiter or full name", 1);
		}

		$name           = array_shift($parts);
		$originalGender = $this->genderizer->getGender($name);

		$whichName      = ($originalGender !== false) ? self::FIRST_NAME_INDEX : self::LAST_NAME_INDEX;
		$splittedNames[$whichName][] = $name;

		$nameChanged = false;
		while ((count($parts) > 0) && !$nameChanged) {
			$name = array_shift($parts);
			if (($lastGender = $this->genderizer->getGender($name)) !== $originalGender) { // cambió
				$nameChanged = true;
				array_unshift($parts, $name);
			} else {
				$splittedNames[$whichName][] = $name;
			}
		}

		$whichName = ($whichName === self::FIRST_NAME_INDEX) ? self::LAST_NAME_INDEX : self::FIRST_NAME_INDEX;
		$splittedNames[$whichName] = $parts;

		return [
			implode(' ', $splittedNames[self::FIRST_NAME_INDEX]), 
			implode(' ', $splittedNames[self::LAST_NAME_INDEX]), 
			($originalGender !== false) ? $originalGender : $lastGender
		];
	}

	public function sortNames($firstName, $lastName)
    {
        if (!($gender = $this->genderizer->getGender($firstName))) { // El primer nombre no devuelve un genero, pruebo con el apellido
            if ($gender = $this->genderizer->getGender($lastName)) {
                $auxFirstName     = $firstName;
                $firstName = $lastName;
                $lastName = $auxFirstName;
            }
        }

        return array($firstName, $lastName, $gender);
    }

	public function prettify($name)
	{
		return ucwords(mb_strtolower(trim($name)));
	}
}