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

        $normalized = trim($fullname);

        if (preg_match('/[.,;\/-]/', $normalized)) {
            $parts = preg_split('/[.,;\/-]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);
            $parts = array_map('trim', $parts);

            if (count($parts) == 2) {
                list($first, $last, $gender) = $this->sortNames($parts[1], $parts[0]);
                return [
                    $this->prettify($first),
                    $this->prettify($last),
                    $gender,
                ];
            }

            $normalized = preg_replace('/[.,;\/-]+/', ' ', $normalized);
        }

        $parts = explode($delimiter, $normalized);

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

        $first = implode(' ', $splittedNames[self::FIRST_NAME_INDEX]);
        $last = implode(' ', $splittedNames[self::LAST_NAME_INDEX]);
        $gender = ($originalGender !== false) ? $originalGender : (isset($lastGender) ? $lastGender : '');

        return [
            $this->prettify($first),
            $this->prettify($last),
            $gender,
        ];
    }

    public function sortNames($firstName, $lastName)
    {
        $gender = $this->genderizer->getGender($firstName);

        if ($gender === false) {
            $lastGender = $this->genderizer->getGender($lastName);
            if ($lastGender !== false) {
                $aux = $firstName;
                $firstName = $lastName;
                $lastName = $aux;
                $gender = $lastGender;
            }
        }

        return [$firstName, $lastName, $gender];
    }

    public function prettify($name)
    {
        return ucwords(mb_strtolower(trim($name)));
    }
}