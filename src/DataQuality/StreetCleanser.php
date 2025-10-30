<?php

namespace WoowUpV2\DataQuality;

class StreetCleanser
{
	const MAX_LENGTH = 100;

	/**
	 * Truncate street field to maximum allowed length
	 *
	 * @param string $street
	 * @return string
	 */
	public function truncate($street)
	{
		if (!is_string($street)) {
			return '';
		}

		return mb_substr($street, 0, self::MAX_LENGTH);
	}
}