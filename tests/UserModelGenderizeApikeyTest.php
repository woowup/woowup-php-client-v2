<?php

/**
 * UserModel: the Genderize API key set with setGenderizeApikey() must not travel in the body
 * of the user POST/PUT, which is built from jsonSerialize().
 *
 * Ejecutar: php tests/UserModelGenderizeApikeyTest.php
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

function buildUser()
{
    $user = new UserModel();
    $user->setServiceUid(null);
    $user->setDocument('30111222');
    $user->setEmail('cliente@example.com');
    $user->setFirstName('Ana');
    $user->setLastName('Garcia');
    $user->setPostcode('7550000');
    $user->setTags('vip');
    $user->setCustomAttributes(['talle' => 'M']);

    return $user;
}

$withoutKey = buildUser();
$withKey    = buildUser();
check($withKey->setGenderizeApikey('x') === $withKey, 'setGenderizeApikey sigue existiendo y devuelve el modelo');

$json       = json_encode($withKey);
$serialized = json_decode($json, true);

echo "\n-- la API key no sale en el payload --\n";
check(!array_key_exists('genderize_apikey', $serialized), 'el JSON no trae genderize_apikey');
check(strpos($json, 'genderize_apikey') === false, 'el texto del JSON no menciona genderize_apikey');
check(!array_key_exists('genderize_apikey', $withKey->jsonSerialize()), 'jsonSerialize(), que arma el body de Users, tampoco la trae');

echo "\n-- el resto de los campos queda igual --\n";
check($json === json_encode($withoutKey), 'el JSON es identico al de un usuario sin la API key');
check(array_key_exists('service_uid', $serialized) && $serialized['service_uid'] === null, 'service_uid null se sigue mandando');
check(($serialized['email'] ?? null) === 'cliente@example.com', 'email se sigue mandando');
check(($serialized['custom_attributes']['talle'] ?? null) === 'M', 'custom_attributes se sigue mandando');
check(!array_key_exists('cleanser', $serialized) && !array_key_exists('countriesHelper', $serialized), 'cleanser y countriesHelper siguen afuera');

echo "\n$passed OK, $failed FAIL\n";
exit($failed > 0 ? 1 : 0);
