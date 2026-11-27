<?php

/**
 * Callers de EmailCleanser::sanitize() fuera de UserModel::setEmail/PurchaseModel/PurchaseCustomerModel.
 *
 * sanitize() devuelve INVALID_EMAIL (noemail@noemail.com) para los usuarios de relleno ("no@mail.com") y
 * para los valores con varias direcciones. Es el placeholder que comparten todos los clientes:
 * - UserModel::setNewEmail lo manda como <documento>@noemail.com, igual que setEmail.
 * - Sucursal, vendedor, familiar, carrito y evento lo rechazan igual que false (el evento queda con email
 *   null, el resto no setea el email). Si el que llama pasa el placeholder a propósito (vendedor default
 *   de Odoo/SiesaSelene en connectors), se sigue guardando.
 *
 * Ejecutar: php tests/ModelsInvalidEmailTest.php
 */

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

use WoowUpV2\Models\AbandonedCartModel;
use WoowUpV2\Models\BranchModel;
use WoowUpV2\Models\FamilyMemberModel;
use WoowUpV2\Models\SellerModel;
use WoowUpV2\Models\UserEventModel;
use WoowUpV2\Models\UserModel;

// Los setters avisan con E_USER_WARNING cuando rechazan: se silencian para que no ensucien la salida.
set_error_handler(function ($errno) {
    return $errno === E_USER_WARNING;
});

$passed = 0;
$failed = 0;

function check($cond, $msg)
{
    global $passed, $failed;
    if ($cond) { $passed++; echo "  OK   $msg\n"; }
    else       { $failed++; echo "  FAIL $msg\n"; }
}

/** Lee la propiedad protegida o privada $prop; null si el modelo nunca la seteó. */
function prop($model, string $prop)
{
    $r = new ReflectionObject($model);
    while (!$r->hasProperty($prop) && $r->getParentClass()) {
        $r = $r->getParentClass();
    }
    $p = $r->getProperty($prop);
    $p->setAccessible(true);
    return $p->isInitialized($model) ? $p->getValue($model) : null;
}

echo "======================================================================\n";
echo " Modelos: INVALID_EMAIL no se guarda como email\n";
echo "======================================================================\n";

echo "\n--- UserModel::setNewEmail ---\n";

$u = (new UserModel())->setDocument('30123456')->setNewEmail('no@mail.com');
check(prop($u, 'new_email') === '30123456@noemail.com', 'no@mail.com con documento -> ' . var_export(prop($u, 'new_email'), true));
check(strpos((string) prop($u, 'tags'), 'email_rejected') !== false, 'queda con el tag email_rejected');

$u = (new UserModel())->setNewEmail('test@gmail.com');
check(prop($u, 'new_email') === 'noemail@noemail.com', 'sin identificador -> ' . var_export(prop($u, 'new_email'), true));

$u = (new UserModel())->setDocument('30123456')->setNewEmail('Ana@Hotmail.com');
check(prop($u, 'new_email') === 'ana@hotmail.com', 'email válido sigue igual -> ' . var_export(prop($u, 'new_email'), true));

echo "\n--- sucursal, vendedor, familiar, carrito y evento ---\n";

$modelos = [
    'BranchModel'        => function () { return new BranchModel(); },
    'SellerModel'        => function () { return new SellerModel(); },
    'FamilyMemberModel'  => function () { return new FamilyMemberModel(); },
    'AbandonedCartModel' => function () { return new AbandonedCartModel(); },
    'UserEventModel'     => function () { return new UserEventModel(); },
];

foreach ($modelos as $nombre => $nuevo) {
    foreach (['no@mail.com', 'test@gmail.com', 'ana@hotmail.com / juan@gmail.com'] as $email) {
        $email_guardado = prop($nuevo()->setEmail($email), 'email');
        check($email_guardado === null, "$nombre $email -> " . var_export($email_guardado, true));
    }

    $email_guardado = prop($nuevo()->setEmail('noemail@noemail.com'), 'email');
    check($email_guardado === 'noemail@noemail.com', "$nombre placeholder pasado a propósito -> " . var_export($email_guardado, true));

    $email_guardado = prop($nuevo()->setEmail('Mail@Empresa.com.ar'), 'email');
    check($email_guardado === 'mail@empresa.com.ar', "$nombre mail@ en dominio propio -> " . var_export($email_guardado, true));
}

echo "\n$passed OK, $failed FAIL\n";
exit($failed === 0 ? 0 : 1);
