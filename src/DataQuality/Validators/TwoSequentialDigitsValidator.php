<?php

namespace WoowUpV2\DataQuality\Validators;

/**
 * Validator to detect phone numbers containing
 * two or more long sequential digit patterns.
 *
 * Works for ascending or descending sequences.
 */
class TwoSequentialDigitsValidator implements ValidatorInterface
{
    const MIN_SEQUENCE_LENGTH = 5; // longitud mínima para contar como secuencia
    const REQUIRED_SEQUENCES = 2;  // numero de secuencias para rechazar

    /**
     * Validate a phone number.
     *
     * Returns false if the number contains two or more sequences
     * (ascending or descending) of at least MIN_SEQUENCE_LENGTH digits.
     *
     * @param string $input Phone number to validate
     * @return bool True if valid, false if two sequences detected
     */
    public function validate(string $input): bool
    {
        $digits = preg_replace('/\D/', '', $input); // eliminar caracteres no numéricos
        $sequencesFound = 0;
        $asc = $desc = 1;

        for ($i = 1, $len = strlen($digits); $i < $len; $i++) {
            $prev = (int)$digits[$i - 1];
            $curr = (int)$digits[$i];

            // ascendente
            if ($curr === $prev + 1) {
                $asc++;
                if ($asc >= self::MIN_SEQUENCE_LENGTH) {
                    $sequencesFound++;
                    $asc = 1; // reiniciar contador para detectar otra secuencia
                    if ($sequencesFound >= self::REQUIRED_SEQUENCES) return false;
                }
            } else {
                $asc = 1;
            }

            // descendente
            if ($curr === $prev - 1) {
                $desc++;
                if ($desc >= self::MIN_SEQUENCE_LENGTH) {
                    $sequencesFound++;
                    $desc = 1; // reiniciar contador
                    if ($sequencesFound >= self::REQUIRED_SEQUENCES) return false;
                }
            } else {
                $desc = 1;
            }
        }

        return true;
    }
}
