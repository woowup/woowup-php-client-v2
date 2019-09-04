<?php
require_once '../vendor/autoload.php';

/**
 * Insertar API key
 */
$apikey = 'xxxxxxx';
$sales  = './ventas.csv';

/**
 * Este script toma las ventas desde un archivo csv, en donde se tiene una fila por cada item comprado.
 *
 * Los pasos de la importación son:
 *      1) Se recorre el archivo y se importan los clientes
 *      2) Se recorre el archivo, se van armando las ventas (una venta puede estar compuesta por mas de una fila) y se arma la
 *          estructura de datos de la venta que entiende la API de WoowUp
 */

define('DUPLICATED_PURCHASE', 'duplicated_purchase_number');
define('INTERNAL_ERROR', 'internal_error');

$woowup   = new \WoowUpV2\Client($apikey);
$imported = [];

/**
 * Primero iteramos todo el csv para ir cargando los clientes
 */
logMessage("Importación de clientes");

if (($fh = fopen($sales, "r")) !== false) {
    while (($row = fgetcsv($fh)) !== false) {
        $email = $row[15];
        if (validEmail($email)) {
            if (in_array($email, $imported)) {
                continue;
            }

            $parts = explode(' ', $row[13]);

            $user = new WoowUp\Models\UserModel();
            $user
                ->setEmail($email)
                ->setFirstName(isset($parts[0]) ? $parts[0] : '')
                ->setLastName(isset($parts[1]) ? $parts[1] : '');

            try {
                if (!$woowup->users->exist(['email' => $user->getEmail()])) {
                    $response = $woowup->users->create($user);

                    logMessage("creado: {$user->getEmail()}");
                } else {
                    logMessage("existente: {$user->getEmail()}");
                    $response = $woowup->users->update($user);
                }

                $imported[] = $email;
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $response = json_decode($e->getResponse()->getBody());

                if ($response->code == INTERNAL_ERROR && $response->message == 'usuario existente') {
                    continue;
                } else {
                    logMessage($e->getResponse()->getBody());
                    die();
                }
            }
        }
    }

    fclose($fh);
}

/**
 * Volvemos a iterar el archivo para cargar ventas
 */
logMessage("Importación de ventas");

if (($fh = fopen($sales, "r")) !== false) {
    $invoice_number = null;
    while (($row = fgetcsv($fh)) !== false) {
        $email = $row[15];

        if (!validEmail($email)) {
            continue;
        }

        if (!is_null($invoice_number) && $invoice_number != $row[2]) {
            $order = buildOrder($orders);

            try {
                $response = $woowup->purchases->create($order);
                $orders   = [];

                logMessage("creada: {$row[2]}");
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $response = json_decode($e->getResponse()->getBody());
                if ($response->code == DUPLICATED_PURCHASE) {
                    logMessage("duplicada: {$row[2]}");
                } else {
                    logMessage($e->getResponse()->getBody());
                    die();
                }
            }
        }

        $orders[]       = $row;
        $invoice_number = $row[2];
    }

    fclose($fh);
}

/**
 * Chequea si un email es válido
 *
 * @param  string $email
 * @return bool
 */
function validEmail($email)
{
    return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Transforma las filas del csv que representan a la venta en el formato aceptado por la api
 * de WoowUp.
 *
 * @param  Array $orders Listado de ventas
 * @return Array]
 */
function buildOrder($orders)
{
    $order = new \WoowUp\Models\PurchaseModel();

    $order
        ->setEmail($orders[0][15])
        ->setPoints(0)
        ->setInvoiceNumber($orders[0][2])
        ->setBranchName($orders[0][0])
        ->setCreatetime(date('Y-m-d H:i:s'))
        ->setChannel('in-store');

    $total    = 0;
    $discount = 0;
    foreach ($orders as $o) {
        $item = new \WoowUp\Models\PurchaseItemModel();
        $item
            ->setSku($o[3])
            ->setProductName($o[4])
            ->setCategory([$o[5], $o[6]])
            ->setQuantity((int) abs($o[16]))
            ->setUnitPrice((float) $o[17])
            ->setVariations([
                ['name' => 'Linea', 'value' => $o[5]],
                ['name' => 'Color', 'value' => $o[10]],
                ['name' => 'Talle', 'value' => $o[11]],
            ]);
        $order->addItem($item);

        $total += (int) abs($o[16]) * (float) $o[17];
        $discount += abs($o[19]);
    }

    $prices = new \WoowUp\Models\PurchasePricesModel();
    $prices
        ->setGross($total)
        ->setDiscount($discount)
        ->setTotal($total - $discount);
    $order->setPrices($prices);

    return $order;
}

/**
 * Dummy log de info
 * @param  string $message Mensaje a loguear
 * @return void
 */
function logMessage($message)
{
    echo date('Y-m-d H:i:s') . ": $message\n";
}
