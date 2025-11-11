<?php

namespace WoowUpV2\DataQuality;

use WoowUpV2\DataQuality\Telephone\TelephoneFormatter;
use WoowUpV2\DataQuality\Validators\ConsecutiveDigitsValidator;
use WoowUpV2\DataQuality\Validators\GenericPhoneValidator;
use WoowUpV2\DataQuality\Validators\LengthValidator;
use WoowUpV2\DataQuality\Validators\NumericValidator;
use WoowUpV2\DataQuality\Validators\RepeatedDigitsValidator;
use WoowUpV2\DataQuality\Validators\SequentialDigitsValidator;
use WoowUpV2\DataQuality\Validators\TwoSequentialDigitsValidator;

/**
 * Telephone number sanitizer and validator
 *
 * Process flow:
 * 1. Type validation and normalization
 * 2. Format and clean using TelephoneFormatter
 * 3. Validate using all validators
 * 4. Return sanitized number or false if invalid
 */
class TelephoneCleanser
{
	/**
	 * @var TelephoneFormatter
	 */
	private $formatter;

	/**
	 * @var array Array of ValidatorInterface instances
	 */
	private $validators;

	public function __construct()
	{
		$this->formatter = new TelephoneFormatter();
		$this->validators = [
			new NumericValidator(),
			new LengthValidator(8, 15), // min: 8, max: 15 (E.164 standard)
			new RepeatedDigitsValidator(), // 5+ same digits: 5555532423
			new ConsecutiveDigitsValidator(), // 6+ consecutive: 11111111111
			new SequentialDigitsValidator(), // 9+ sequential patterns: 987654321, 1234567890
			new GenericPhoneValidator(), // Known generic/placeholder numbers
		];

		return $this;
	}

	/**
	 * Sanitize telephone number
	 *
	 * Steps:
	 * 1. Type validation and normalization
	 * 2. Format and clean telephone
	 * 3. Validate through all validators
	 *
	 * @param mixed $telephone Raw telephone input
	 * @return string|false Returns sanitized telephone (digits only) or false on failure
	 */
	public function sanitize($telephone)
	{
		if (!is_string($telephone) && !is_numeric($telephone)) {
			return false;
		}

		$telephone = (string) $telephone;
		$telephone = trim($telephone);

		if ($telephone === '') {
			return false;
		}

		$cleanedTelephone = $this->formatter->clean($telephone);

		if ($cleanedTelephone === '') {
			return false;
		}

		foreach ($this->validators as $validator) {
			if (!$validator->validate($cleanedTelephone)) {
				return false;
			}
		}

		return $cleanedTelephone;
	}

    /**
     * Check if telephone is valid
     *
     * @param mixed $telephone
     * @return bool
     */
	public function isValid($telephone): bool
	{
		return $this->sanitize($telephone) !== false;
	}
}