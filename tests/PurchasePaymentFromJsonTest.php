<?php

// Run: php tests/PurchasePaymentFromJsonTest.php
//
// Every PHP diagnostic is turned into an ErrorException, as Lumen does: a purchase built from
// JSON with `payment` used to raise E_DEPRECATED on PHP 7 and an Error on PHP 8.

// Autoloads src/ directly so the test runs with plain `php`, without composer install.
spl_autoload_register(function ($class) {
    $prefix = 'WoowUpV2\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

use WoowUpV2\Models\PurchaseModel;
use WoowUpV2\Models\PurchasePaymentModel;

error_reporting(-1);
set_error_handler(function ($errno, $message, $file, $line) {
    throw new \ErrorException($message, 0, $errno, $file, $line);
});

$passed = 0;
$failed = 0;

function check($cond, $msg)
{
    global $passed, $failed;
    if ($cond) { $passed++; echo "  OK   $msg\n"; }
    else       { $failed++; echo "  FAIL $msg\n"; }
}

function fromJson(array $data)
{
    try {
        return PurchaseModel::createFromJson(json_encode($data));
    } catch (\Throwable $e) {
        echo '       ' . get_class($e) . ': ' . $e->getMessage() . "\n";
        return null;
    }
}

// Shaped like the purchase that WoowUp serializes for webhooks and GET /purchases.
function purchaseData(array $extra = [])
{
    return array_merge([
        'invoice_number'  => 'A-0001-00012345',
        'document'        => '30111222',
        'email'           => 'ana@example.com',
        'channel'         => 'in-store',
        'createtime'      => '2026-10-30 12:00:00',
        'downloadtime'    => '2026-10-30 12:05:00',
        'branch'          => ['id' => 7, 'name' => 'Sucursal Centro'],
        'purchase_detail' => ['items' => [['sku' => 'SKU-1', 'product_name' => 'Crema', 'quantity' => 2, 'unit_price' => 1500.5]]],
        'prices'          => ['total' => 3001, 'gross' => 3001, 'discount' => 0, 'shipping' => 0, 'tax' => 0, 'cost' => 0],
        'points'          => 120,
    ], $extra);
}

echo "\n-- one payment, as an object --\n";
$purchase = fromJson(purchaseData(['payment' => ['type' => 'credit', 'brand' => 'visa', 'name' => 'BANCO GALICIA']]));
check($purchase instanceof PurchaseModel, 'builds the purchase without a PHP diagnostic');
$payment = $purchase ? $purchase->getPayment() : null;
check($payment instanceof PurchasePaymentModel, 'the payment survives as a PurchasePaymentModel');
check($payment && $payment->getType() === 'credit' && $payment->getBrand() === 'visa', 'keeps type and brand');
check($payment && $payment->getBank() === 'Banco Galicia', 'name goes to bank, prettified (' . ($payment ? $payment->getBank() : '-') . ')');
$json = $purchase ? json_decode(json_encode($purchase), true) : [];
check(isset($json['payment']) && $json['payment'] === ['type' => 'credit', 'brand' => 'visa', 'bank' => 'Banco Galicia'], 'the purchase serializes the payment');

echo "\n-- several payments, as an array --\n";
$purchase = fromJson(purchaseData(['payment' => [
    ['type' => 'credit', 'brand' => 'visa', 'name' => 'galicia', 'total' => '2000.50', 'installments' => '3'],
    ['type' => 'cash', 'brand' => null, 'name' => null, 'total' => 1000.5, 'installments' => null],
]]));
$payments = $purchase ? $purchase->getPayment() : null;
check(is_array($payments) && count($payments) === 2, 'both payments survive');
check(is_array($payments) && $payments[0]->getTotal() === 2000.5 && $payments[0]->getInstallments() === 3, 'total is a float and installments an int');
check(is_array($payments) && $payments[1]->getType() === 'cash' && $payments[1]->getTotal() === 1000.5, 'keeps the second payment');

echo "\n-- no payment --\n";
$without = fromJson(purchaseData());
$withNull = fromJson(purchaseData(['payment' => null]));
check($without instanceof PurchaseModel && $withNull instanceof PurchaseModel, 'builds both purchases without a PHP diagnostic');
check($without && !array_key_exists('payment', json_decode(json_encode($without), true)), 'a purchase without payment serializes no payment');
check($without && $withNull && json_encode($without) === json_encode($withNull), 'payment null serializes like no payment');

echo "\n-- unchanged behaviour --\n";
try {
    PurchaseModel::createFromJson(json_encode(purchaseData(['payment' => ['type' => 'transfer']])));
    check(false, 'a payment type the model does not know still throws');
} catch (\ErrorException $e) {
    check(false, 'a payment type the model does not know still throws (got a PHP diagnostic: ' . $e->getMessage() . ')');
} catch (\Exception $e) {
    check($e->getMessage() === 'Invalid payment: invalid type', 'a payment type the model does not know still throws');
} catch (\Throwable $e) {
    check(false, 'a payment type the model does not know still throws (got ' . get_class($e) . ': ' . $e->getMessage() . ')');
}
try {
    $static = PurchasePaymentModel::createFromJson(json_encode(['type' => 'debit', 'total' => 10]));
    check($static->getType() === 'debit' && $static->getTotal() === 10.0, 'PurchasePaymentModel::createFromJson works statically');
} catch (\Throwable $e) {
    $static = null;
    check(false, 'PurchasePaymentModel::createFromJson works statically (' . get_class($e) . ': ' . $e->getMessage() . ')');
}
$onInstance = (new PurchasePaymentModel())->createFromJson(json_encode(['type' => 'debit', 'total' => 10]));
check($onInstance->getType() === 'debit' && $onInstance->getTotal() === 10.0, 'and still works when called on an instance');

$autoload = getenv('GUZZLE_AUTOLOAD') ?: __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    echo "\n-- Purchases::find: SKIP, no Guzzle (pass GUZZLE_AUTOLOAD) --\n";
} else {
    require_once $autoload;

    echo "\n-- Purchases::find --\n";
    $body = json_encode(['payload' => [purchaseData(['payment' => [['type' => 'debit', 'brand' => 'maestro', 'name' => 'nacion', 'total' => 3001, 'installments' => 1]]])], 'code' => 'ok']);
    $stack = \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([
        new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], $body),
    ]));
    $woowup = new \WoowUpV2\Client('apikey', 'https://api.woowup.com', 'apiv3', new \GuzzleHttp\Client(['handler' => $stack]));
    try {
        $found = $woowup->purchases->find('A-0001-00012345');
        check($found instanceof PurchaseModel && $found->getPayment() instanceof PurchasePaymentModel, 'a purchase from the API keeps its payment');
    } catch (\Throwable $e) {
        check(false, 'a purchase from the API keeps its payment (' . get_class($e) . ': ' . $e->getMessage() . ')');
    }
}

echo "\n$passed OK, $failed FAIL\n";
exit($failed > 0 ? 1 : 0);
