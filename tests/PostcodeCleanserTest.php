<?php

/**
 * PostcodeCleanser: the API rejects a postcode longer than 16 with a 400, and on user creation
 * that drops the customer and every purchase waiting for it. Stores that type the commune or a
 * pickup point in the zip field (Blockstore 1897: "PEDRO AGUIRRE CERDA") hit it routinely.
 *
 * Checks the cleanser on its own and what UserModel serializes after setPostcode().
 *
 * Ejecutar: php tests/PostcodeCleanserTest.php
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

use WoowUpV2\DataQuality\PostcodeCleanser;
use WoowUpV2\Models\UserModel;

$passed = 0;
$failed = 0;

function check($cond, $msg)
{
    global $passed, $failed;
    if ($cond) { $passed++; echo "  OK   $msg\n"; }
    else       { $failed++; echo "  FAIL $msg\n"; }
}

echo "\n-- el cleanser --\n";
$cleanser = new PostcodeCleanser();
check($cleanser->truncate('7550000') === '7550000', 'un codigo postal real queda igual');
check($cleanser->truncate('LAS CONDES') === 'LAS CONDES', 'un texto de hasta 16 queda igual');
check($cleanser->truncate('SAN PEDRO DE LA PAZ') === 'SAN PEDRO DE LA', '"SAN PEDRO DE LA PAZ" (19) se corta a 16 sin el espacio final');
check($cleanser->truncate('BLOCKINDEPENDENCIA') === 'BLOCKINDEPENDENC', '"BLOCKINDEPENDENCIA" (18) se corta a 16');
check($cleanser->truncate(null) === '', 'un valor no string vuelve vacio, igual que street');
$withAccents = $cleanser->truncate('ÑUÑOA ÑUÑOA ÑUÑOA ÑUÑOA');
check(strlen(json_encode($withAccents)) - 2 <= 16, 'con eñes se mide por el largo en JSON, igual que street');

echo "\n-- UserModel --\n";
$user = new UserModel();
$user->setEmail('cliente@example.com');
$user->setPostcode('PEDRO AGUIRRE CERDA');
check($user->getPostcode() === 'PEDRO AGUIRRE CE', 'setPostcode guarda el valor recortado (' . $user->getPostcode() . ')');
$serialized = json_decode(json_encode($user), true);
check(($serialized['postcode'] ?? null) === 'PEDRO AGUIRRE CE', 'el JSON que se manda a la API lleva el postcode recortado');
$user->setPostcode('7550000');
check($user->getPostcode() === '7550000', 'un codigo postal real pasa intacto por el modelo');

echo "\n$passed OK, $failed FAIL\n";
exit($failed > 0 ? 1 : 0);
