<?php

namespace WoowUpV2\DataQuality\Validators;

class GenericEmailValidator
{
    private const GENERIC_EMAILS = [
        'correo',
        'notiene',
        'nocontacto',
        'unavailable',
        'noautoriza',
        'example',
        'ejemplo',
        'facturacion.electronica',
        'factura.electronica',
        'noemail',
        'prueba',
        'nomail',
        'notmail',
        'ninguno'
    ];

    public function validate(string $email): bool
    {
        return !array_filter(
            self::GENERIC_EMAILS,
            fn (string $term): bool => stripos($email, $term) !== false
        );
    }
}