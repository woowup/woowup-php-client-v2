<?php

namespace WoowUpV2\DataQuality\Validators;

/**
 * Generic validator interface
 *
 * Used for validating any string data (telephone, email, document, etc.)
 */
interface ValidatorInterface
{
	/**
	 * Validate input string
	 *
	 * @param string $input The input string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validate(string $input): bool;
}