<?php

/**
 * Sale with customer (POST /purchases/with-customer and /purchases/bulk/with-customer): the
 * PurchaseCustomerModel writes in raw the status of what the cleanser did with each email and
 * telephone, and PurchaseModel carries it in `customer`.
 *
 * The endpoint checks need Guzzle: they use vendor/autoload.php when there is one, or the
 * autoloader passed in GUZZLE_AUTOLOAD, and are skipped otherwise.
 *
 * Ejecutar: php tests/PurchaseWithCustomerTest.php
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

use WoowUpV2\Models\PurchaseCustomerModel;
use WoowUpV2\Models\PurchaseItemModel;
use WoowUpV2\Models\PurchaseModel;
use WoowUpV2\Models\PurchasePricesModel;

// Los setters avisan con E_USER_WARNING cuando descartan un dato; aca se miran los estados.
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

function customerWithEmail($email, $document = null)
{
    $customer = new PurchaseCustomerModel();
    if ($document !== null) {
        $customer->setDocument($document);
    }

    return $customer->setEmail($email);
}

function purchase($invoiceNumber, PurchaseCustomerModel $customer = null)
{
    $purchase = new PurchaseModel();
    $purchase->setInvoiceNumber($invoiceNumber)
        ->setChannel(PurchaseModel::CHANNEL_IN_STORE)
        ->setBranchName('Unicentro')
        ->setCreatetime('2026-10-08 14:35:22')
        ->setPrices((new PurchasePricesModel())->setTotal(459900))
        ->addItem((new PurchaseItemModel())->setSku('NF0A3XEO')->setQuantity(1)->setUnitPrice(459900));
    if ($customer !== null) {
        $purchase->setCustomer($customer);
    }

    return $purchase;
}

echo "\n-- email_status --\n";
$c = customerWithEmail('ana@hotmail.com');
check($c->getEmail() === 'ana@hotmail.com' && $c->getEmailStatus() === 'validated', 'un email que el cleanser deja igual es validated');
$c = customerWithEmail('Ana@Hotmail.com ');
check($c->getEmail() === 'ana@hotmail.com' && $c->getEmailStatus() === 'cleaned', 'mayusculas y espacios ya cuentan como cleaned (' . $c->getEmail() . ')');
check($c->getRaw()['email'] === 'Ana@Hotmail.com ', 'raw.email guarda el valor tal como se cargo');
$c = customerWithEmail('ana.perez@gmial.com');
check($c->getEmail() === 'ana.perez@gmail.com' && $c->getEmailStatus() === 'cleaned', 'un typo de gmail corregido es cleaned');
$c = customerWithEmail('juan sin mail', '30111222');
check($c->getEmail() === '30111222@noemail.com' && $c->getEmailStatus() === 'rejected', 'sin formato va <documento>@noemail.com con rejected (' . $c->getEmail() . ')');
check($c->getRaw()['email'] === 'juan sin mail', 'el email rechazado queda en raw');
$c = customerWithEmail('juan@hotmail');
check($c->getEmailStatus() === 'rejected', 'un email sin TLD no tiene formato y es rejected');
$c = (new PurchaseCustomerModel())->setTelephone('3011528226')->setEmail('nada');
check($c->getEmail() === '3011528226@noemail.com', 'sin documento ni service_uid el @noemail se arma con el telefono');
$c = customerWithEmail('nada');
check($c->getEmail() === 'noemail@noemail.com', 'sin ningun id queda noemail@noemail.com');
$c = customerWithEmail('maria@hotmail.com / juan@gmail.com', '30111222');
check($c->getEmailStatus() === 'rejected', 'varias direcciones en un valor es rejected');
$c = customerWithEmail('30111222@noemail.com');
check($c->getEmail() === '30111222@noemail.com' && $c->getEmailStatus() === 'generic', 'un @noemail.com que viene de la fuente es generic');
$c = customerWithEmail('JUAN.123@mail.mercadolibre.com');
check($c->getEmail() === 'juan.123@mail.mercadolibre.com' && $c->getEmailStatus() === 'generic', 'una direccion proxy de mercadolibre es generic');
$c = (new PurchaseCustomerModel())->setDocument('30111222')->setEmail('nada', true);
check($c->getEmail() === '30111222@noemail.com', 'setEmail($x, true) sanitiza y no apaga el <id>@noemail.com, igual que PurchaseModel');
$c = (new PurchaseCustomerModel())->setDocument('30111222')->setEmail('nada', true, true);
check($c->getEmail() === 'noemail@noemail.com', 'el tercer parametro apaga el <id>@noemail.com, igual que PurchaseModel');
$c = (new PurchaseCustomerModel())->setDocument('30111222')->setEmail('nada')->setEmail('Ana@Hotmail.com', false);
check($c->getEmail() === 'Ana@Hotmail.com' && $c->getEmailStatus() === null, 'sin sanitizar queda tal cual y sin email_status');
check($c->getRaw()['email'] === 'Ana@Hotmail.com', 'sin sanitizar el valor igual queda en raw');
$c = customerWithEmail(str_repeat('a', 140) . ' sin arroba');
check(strlen($c->getRaw()['email']) === 128, 'raw.email se corta a los 128 que acepta la API');

echo "\n-- telephone_status --\n";
$c = (new PurchaseCustomerModel())->setTelephone('3011528226');
check($c->getTelephone() === '3011528226' && $c->getTelephoneStatus() === 'validated', 'un telefono que queda igual es validated');
$c = (new PurchaseCustomerModel())->setTelephone('+57 301 152 8226');
check($c->getTelephone() === '+573011528226' && $c->getTelephoneStatus() === 'cleaned', 'un telefono reformateado es cleaned (' . $c->getTelephone() . ')');
$c = (new PurchaseCustomerModel())->setDocument('30111222')->setTelephone('1111111111');
check($c->getTelephone() === null && $c->getTelephoneStatus() === 'rejected', 'un telefono descartado es rejected y no va en customer');
check(!isset(json_decode(json_encode($c), true)['telephone']), 'el telefono rechazado no se serializa');
check($c->getRaw()['telephone'] === '1111111111', 'el telefono rechazado queda en raw');
$c = (new PurchaseCustomerModel())->setTelephone('+57 301 152 8226', true);
check($c->getTelephone() === '+573011528226' && $c->getTelephoneStatus() === 'cleaned', 'setTelephone($x, true) sanitiza, igual que PurchaseModel');
$c = (new PurchaseCustomerModel())->setTelephone('1111111111')->setTelephone('+57 301 152 8226', false);
check($c->getTelephone() === '+57 301 152 8226' && $c->getTelephoneStatus() === null, 'setTelephone($x, false) queda tal cual y sin telephone_status');
check($c->getRaw()['telephone'] === '+57 301 152 8226', 'sin sanitizar el telefono igual queda en raw');
$c = (new PurchaseCustomerModel())->setDocument('30111222')->setTelephone('11+22');
check($c->getTelephoneStatus() === 'rejected', 'un telefono con operadores que rechaza la API es rejected');

echo "\n-- validate y JSON --\n";
$c = (new PurchaseCustomerModel())->setTelephone('1111111111');
check($c->validate() === false, 'un cliente cuyo unico id se descarto no es valido');
$c = (new PurchaseCustomerModel())->setDocument('30111222')->setTelephone('+57 301 152 8226')->setEmail('Ana.Perez@Gmail.com');
$json = json_decode(json_encode($c), true);
check(array_keys($json) === ['document', 'email', 'telephone', 'raw'], 'serializa los ids limpios y raw');
check($json['raw']['document'] === '30111222', 'raw lleva el documento tal como se cargo');
check(!array_key_exists('cleanser', $json), 'no serializa el cleanser');

echo "\n-- PurchaseModel --\n";
$purchase = purchase('A-1', $c);
check($purchase->getCustomer() === $c, 'setCustomer cuelga el cliente de la venta');
check(json_decode(json_encode($purchase), true)['customer']['document'] === '30111222', 'la venta serializa customer');
check($purchase->validateWithCustomer() === true, 'validateWithCustomer no pide ids en la raiz');
try {
    $purchase->validate();
    check(false, 'validate sigue pidiendo ids en la raiz');
} catch (\Exception $e) {
    check(true, 'validate sigue pidiendo ids en la raiz');
}
try {
    purchase('A-2')->validateWithCustomer();
    check(false, 'validateWithCustomer exige un customer');
} catch (\Exception $e) {
    check(true, 'validateWithCustomer exige un customer');
}
$noCustomer = purchase('A-3', (new PurchaseCustomerModel())->setTelephone('1111111111'));
check($noCustomer->getCustomer() === null, 'setCustomer no cuelga un cliente sin ids');

$autoload = getenv('GUZZLE_AUTOLOAD') ?: __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    echo "\n-- endpoints: SKIP, sin Guzzle (pasar GUZZLE_AUTOLOAD) --\n";
} else {
    require_once $autoload;

    $history = [];
    $client = function (array $responses) use (&$history) {
        $stack = \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler($responses));
        $stack->push(\GuzzleHttp\Middleware::history($history));

        return new \WoowUpV2\Client('apikey', 'https://api.woowup.com', 'apiv3', new \GuzzleHttp\Client(['handler' => $stack]));
    };
    $json = function ($status, $body) {
        return new \GuzzleHttp\Psr7\Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    };

    echo "\n-- Purchases::createWithCustomer --\n";
    $woowup = $client([$json(200, ['payload' => ['userapp_id' => 253857145, 'customer_created' => true], 'code' => 'ok'])]);
    $payload = $woowup->purchases->createWithCustomer(purchase('A-1', $c));
    check($payload->userapp_id === 253857145 && $payload->customer_created === true, 'devuelve el payload con userapp_id y customer_created');
    $request = end($history)['request'];
    check($request->getMethod() === 'POST' && (string) $request->getUri() === 'https://api.woowup.com/apiv3/purchases/with-customer', 'hace POST a /apiv3/purchases/with-customer');
    $body = json_decode((string) $request->getBody(), true);
    check($body['customer']['raw']['email_status'] === 'cleaned' && $body['invoice_number'] === 'A-1', 'manda la venta con customer y raw');

    $woowup = $client([$json(400, ['payload' => [], 'code' => 'duplicated_purchase_number'])]);
    try {
        $woowup->purchases->createWithCustomer(purchase('A-1', $c));
        check(false, 'un 400 tira RequestException');
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        $code = json_decode((string) $e->getResponse()->getBody())->code;
        check($code === 'duplicated_purchase_number', 'un 400 tira RequestException con el code en el body');
    }

    $woowup = $client([$json(500, ['payload' => [], 'code' => 'customer_not_saved'])]);
    try {
        $woowup->purchases->createWithCustomer(purchase('A-1', $c));
        check(false, 'customer_not_saved tira RequestException sin reintentar');
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        check(json_decode((string) $e->getResponse()->getBody())->code === 'customer_not_saved', 'customer_not_saved tira RequestException sin reintentar');
    }

    echo "\n-- Purchases::bulkCreateWithCustomer --\n";
    $history = [];
    $woowup = $client([$json(207, ['payload' => [
        'errors'    => [['invoice_number' => 'A-2', 'code' => 'duplicated_purchase_number']],
        'customers' => [['invoice_number' => 'A-1', 'userapp_id' => 253857145, 'customer_created' => true]],
    ], 'code' => 'partial'])]);
    $payload = $woowup->purchases->bulkCreateWithCustomer(['x' => purchase('A-1', $c), 'y' => purchase('A-2', $c)]);
    check($payload->errors[0]->code === 'duplicated_purchase_number' && $payload->customers[0]->userapp_id === 253857145, 'un 207 devuelve errors y customers sin tirar');
    $request = end($history)['request'];
    check((string) $request->getUri() === 'https://api.woowup.com/apiv3/purchases/bulk/with-customer', 'hace POST a /apiv3/purchases/bulk/with-customer');
    check(array_keys(json_decode((string) $request->getBody(), true)) === [0, 1], 'manda el lote como array aunque venga con claves');

    $history = [];
    try {
        $woowup->purchases->bulkCreateWithCustomer([purchase('A-1', $c), purchase('A-2')]);
        check(false, 'una venta sin customer frena el lote antes de mandarlo');
    } catch (\Exception $e) {
        check(empty($history), 'una venta sin customer frena el lote antes de mandarlo');
    }
}

echo "\n$passed OK, $failed FAIL\n";
exit($failed > 0 ? 1 : 0);
