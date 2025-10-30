<?php

namespace WoowUpV2\DataQuality;

class StreetCleanser
{
	const MAX_LENGTH = 100;

	/**
	 * Sanitize street field by limiting length and prettifying
	 *
	 * @param string $street
	 * @return string
	 */
	public function sanitize($street)
	{
		if (!is_string($street) || $street === '') {
			return null;
		}

		$street = mb_substr($street, 0, self::MAX_LENGTH);

        return ucwords(mb_strtolower(trim($street)));
	}
}