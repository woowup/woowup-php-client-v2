<?php

namespace WoowUpV2\DataQuality;

use WoowUpV2\DataQuality\Telephone\TelephoneFormatter;
use WoowUpV2\DataQuality\Validators\GenericPhoneValidator;
use WoowUpV2\DataQuality\Validators\LengthValidator;
use WoowUpV2\DataQuality\Validators\NumericValidator;
use WoowUpV2\DataQuality\Validators\RepeatedValidator;
use WoowUpV2\DataQuality\Validators\SequenceValidator;

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
			new RepeatedValidator(5, true), // 5+ same digits: 5555532423
			new SequenceValidator(8, true), // 9+ sequential patterns: 987654321, 1234567890
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

		if ($this->hasInvalidArithmeticOperators($telephone)) {
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

	/**
	 * Check if telephone contains patterns that the API rejects
	 *
	 * These patterns cause the API to reject the entire request, so we need
	 * to detect them early and skip processing entirely (no tags, no validation).
	 *
	 * @param mixed $telephone Telephone to check
	 * @return bool True if contains API-rejected patterns, false otherwise
	 */
	public function hasApiRejectedPatterns($telephone): bool
	{
		if (!is_string($telephone) && !is_numeric($telephone)) {
			return false;
		}

		$telephone = (string) $telephone;
		$telephone = trim($telephone);

		if ($telephone === '') {
			return false;
		}

		return $this->hasInvalidArithmeticOperators($telephone);
	}

	/**
	 * Check if telephone contains invalid arithmetic operators
	 *
	 * @param string $telephone Telephone to check
	 * @return bool True if contains invalid operators, false otherwise
	 */
	private function hasInvalidArithmeticOperators(string $telephone): bool
	{
		if (preg_match('/\d\+\d/', $telephone)) {
			return true;
		}

		return false;
	}
}