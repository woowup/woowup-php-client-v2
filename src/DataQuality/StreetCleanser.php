<?php

namespace WoowUpV2\DataQuality;

class StreetCleanser
{
	const MAX_LENGTH = 100;

	/**
	 * Truncate street field to maximum allowed length
	 * Considers JSON serialization length to handle special characters
	 *
	 * @param string $street
	 * @return string
	 */
	public function truncate($street)
	{
		if (!is_string($street)) {
			return '';
		}

		if ($this->isWithinLimit($street)) {
			return $street;
		}

		return $this->truncateToFit($street);
	}

	/**
	 * Check if string is within JSON length limit
	 *
	 * @param string $street
	 * @return bool
	 */
	private function isWithinLimit($street)
	{
		return $this->getJsonLength($street) <= self::MAX_LENGTH;
	}

	/**
	 * Get JSON encoded length without quotes
	 *
	 * @param string $street
	 * @return int
	 */
	private function getJsonLength($street)
	{
		return strlen(json_encode($street)) - 2;
	}

	/**
	 * Truncate string iteratively until it fits within limit
	 *
	 * @param string $street
	 * @return string
	 */
	private function truncateToFit($street)
	{
		while (mb_strlen($street) > 0) {
			$street = mb_substr($street, 0, mb_strlen($street) - 1);

			if ($this->isWithinLimit($street)) {
				break;
			}
		}

		return $street;
	}
}