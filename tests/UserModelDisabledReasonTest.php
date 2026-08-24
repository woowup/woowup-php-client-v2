<?php

/**
 * El campo *_enabled_reason es TEXTO LIBRE, no un enum.
 *
 * Los setters de reason validaban contra DISABLED_REASON_VALUES por igualdad exacta y
 * descartaban el valor si no matcheaba. Como el backend guarda "{evento}-{fecha}" ante un
 * evento de mailing, y el detalle del proveedor cuando el evento lo trae, el modelo perdia
 * en silencio (bueno, con un E_USER_WARNING) casi todos los opt-out reales al hidratarse
 * desde la respuesta de la API.
 *
 * Ejecutar: php tests/UserModelDisabledReasonTest.php
 */

// Autoloader propio sobre src/: el test corre con `php` plano, sin composer install
// y sin depender de que haya un vendor instalado.
spl_autoload_register(function ($class) {
    $prefijo = 'WoowUpV2\\';
    if (strpos($class, $prefijo) !== 0) {
        return;
    }
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefijo))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

use WoowUpV2\Models\UserModel;

$passed = 0;
$failed = 0;

function check($cond, $msg)
{
    global $passed, $failed;
    if ($cond) { $passed++; echo "  OK   $msg\n"; }
    else       { $failed++; echo "  FAIL $msg\n"; }
}

echo "======================================================================\n";
echo " UserModel: *_enabled_reason es texto libre\n";
echo "======================================================================\n";

// ------------------------------------------------------------------ hidratacion
echo "\n--- createFromJson conserva el reason tal como lo manda la API ---\n";

$reales = [
    'bounce',
    'other',
    'INVALID_EMAIL',
    'spamreport',
    'dropped',
    'unsubscribe',
    'unsubscribe-2025-10-09 20:55:21',
    'unsubscribe-2023-08-08 11:02:33',
    'Amazon SES did not send the message',
    'smtp; 550 5.7.1 <DATA>: Data command rejected',
];

foreach ($reales as $reason) {
    foreach (['mailing', 'sms', 'whatsapp'] as $canal) {
        $u = UserModel::createFromJson(json_encode([$canal . '_enabled_reason' => $reason]));
        $getter = 'get' . ['mailing' => 'Mailing', 'sms' => 'Sms', 'whatsapp' => 'Whatsapp'][$canal] . 'DisabledReason';
        check($u->$getter() === $reason, sprintf("%-9s %s", $canal, substr($reason, 0, 42)));
    }
}

// ------------------------------------------------------------------ setters directos
echo "\n--- los setters no descartan ni emiten warning ---\n";

$warnings = [];
set_error_handler(function ($no, $msg) use (&$warnings) { $warnings[] = $msg; return true; });

$u = new UserModel();
$u->setMailingDisabledReason('unsubscribe-2025-01-01');
$u->setSmsDisabledReason('Amazon SES did not send the message');
$u->setWhatsappDisabledReason('smtp; 550 5.7.1 rejected');

restore_error_handler();

check($u->getMailingDisabledReason()  === 'unsubscribe-2025-01-01', 'mailing conserva el valor');
check($u->getSmsDisabledReason()      === 'Amazon SES did not send the message', 'sms conserva el valor');
check($u->getWhatsappDisabledReason() === 'smtp; 550 5.7.1 rejected', 'whatsapp conserva el valor');
check($warnings === [], 'no se emitio ningun warning (habia 3)');

// ------------------------------------------------------------------ no-regresion
echo "\n--- no-regresion: los valores canonicos siguen funcionando ---\n";

foreach (['bounce', 'other', 'INVALID_EMAIL', 'spamreport', 'dropped'] as $canonico) {
    $u = new UserModel();
    $u->setMailingDisabledReason($canonico);
    check($u->getMailingDisabledReason() === $canonico, "'$canonico' sigue guardandose");
}

echo "\n--- el typo de la lista quedo corregido ---\n";
check(in_array('unsubscribe', UserModel::DISABLED_REASON_VALUES, true), "la lista dice 'unsubscribe'");
check(!in_array('unsuscribe', UserModel::DISABLED_REASON_VALUES, true), "ya no dice 'unsuscribe'");

echo "\n--- serializacion ---\n";
$u = new UserModel();
$u->setEmail('t@t.com');
$u->setMailingDisabledReason('unsubscribe-2025-01-01');
$j = $u->jsonSerialize();
check(isset($j['mailing_disabled_reason']) || isset($j['mailing_enabled_reason']),
    'el reason sale en jsonSerialize()');

echo "\n======================================================================\n";
echo "Results: $passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);
