<?php

namespace WoowUpV2\DataQuality;

use Mailcheck\Mailcheck as Mailcheck;
use WoowUpV2\DataQuality\Formatters\EmailFormatter;
use WoowUpV2\DataQuality\Validators\LengthValidator;
use WoowUpV2\DataQuality\Validators\RepeatedValidator;
use WoowUpV2\DataQuality\Validators\SequenceValidator;

class EmailCleanser
{
	const GENERIC_TLDS    = ["com", "net", "org", "info", "edu", "gov", "mil"];
	const GEOGRAPHIC_TLDS = ["ar", "es", "co", "pe", "bo", "br", "fr", "do", "co.uk"];

    const GMAIL_DOMAINS = [
        'gmail',
        'gamil', 'gmial', 'gmai', 'gmal', 'gnail', 'gmaul', 'gmaol', 'gmaik', 'gmaio',
        'gmeil', 'gmeel', 'gmel',
        'gmaill', 'gmil', 'ggmail', 'gmmail', 'gmailm',
        'gemail', 'gaiml', 'gail', 'gmailcom', 'gmailcomcom',
    ];

    /**
     * @var EmailFormatter
     */
    private $formatter;
    private $emailUser;
    private $emailDomain;

	public function __construct()
	{
        $this->formatter = new EmailFormatter();
        $this->validators = [
            new LengthValidator(6, 30),
            new RepeatedValidator(8, false),
            new SequenceValidator(7, false)
        ];
        $this->emailDomain = null;
	    $this->emailUser   = null;
    }

	public function sanitize($email)
	{
        $this->extractEmailParts($email);

        if ($this->emailUser !== null || $this->emailDomain !== null) {
            $this->emailUser  = strtolower(trim($this->emailUser));
        }

        $cleanedUserEmail = $this->formatter->clean($this->emailUser);

        if ($cleanedUserEmail === '') {
            return false;
        }

        $this->emailUser = $cleanedUserEmail;
        $sanitizedEmail =  $cleanedUserEmail.$this->emailDomain;

        return self::prettify($sanitizedEmail);
	}

	public function validate($email)
	{
		return ((filter_var($email, FILTER_VALIDATE_EMAIL) !== false) && (strpos($email, "@noemail.com") === false));
	}

	public function prettify($email)
	{
		return utf8_encode(mb_strtolower(trim($email)));
	}

	protected function buildPopularTlds()
	{
		$popularTlds = array_merge(self::GENERIC_TLDS, self::GEOGRAPHIC_TLDS);
		foreach (self::GENERIC_TLDS as $genericTld) {
			foreach (self::GEOGRAPHIC_TLDS as $geoTld) {
				$popularTlds[] = $genericTld . "." . $geoTld;
			}
		}

		return $popularTlds;
	}

    protected function extractEmailParts(string $email): void
    {
        foreach (self::GMAIL_DOMAIN as $knownDomain) {
            $pos = strpos($email, $knownDomain);
            if ($pos !== false) {
                // Lo de la izquierda es user
                $this->emailUser   = substr($email, 0, $pos);
                // La coincidencia y lo que sigue es domain
                $this->emailDomain = '@gmail.com';
                return;
            }
        }

        // Si no hay @ válido ni coincidencia de dominio
        $this->emailUser   = null;
        $this->emailDomain = null;
    }

    public function getEmailUser(): ?string
    {
        return $this->emailUser;
    }

    public function getEmailDomain(): ?string
    {
        return $this->emailDomain;
    }

}