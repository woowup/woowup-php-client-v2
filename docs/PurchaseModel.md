Represents a purchase

Before checking this model out, it is recommended to take a look at the following:
+ [PurchaseItemModel documentation](PurchaseItemModel.md)
+ [PurchasePricesModel documentation](PurchasePricesModel.md)
+ [PurchasePaymentModel documentation](PurchasePaymentModel.md)
+ [SellerModel documentation](SellerModel.md) (optional)

## List of methods

| Field in API | Setter in php-client-v2 | Getter in php-client-v2 | Comments |
| --- | --- | --- | --- |
| invoice_number | setInvoiceNumber(*string* $invoice_number) | getInvoiceNumber() | Purchase identifier |
| service_uid | setServiceUid(*string* $service_uid) | getServiceUid() | |
| email | setEmail(*string* email) | getEmail() | |
| document | setDocument(*string* $document) | getDocument() | |
| points | setPoints(*int* $points) | getPoints() | Loyalty points rewarded for the purchase |
| channel | setChannel(*string* $channel) | getChannel() | Valid values: 'web', 'telephone', 'in-store', 'corporate', 'direct', 'other'. |
| purchase_detail | setPurchaseDetail(*array* $purchase_detail) <br> addItem(*\WoowUp\Models\PurchaseItemModel* $item) | getPurchaseDetail() | |
| prices | setPrices(*\WoowUp\Models\PurchasePricesModel* $prices) | getPrices() | |
| payment | setPayment(*array* $payments) | getPayment() | Sets several payments. Must be an array of PurchasePaymentModel and every payment must have 'type' and 'total' |
| payment | setPayment(*\WoowUp\Models\PurchasePaymentModel* $payment) | getPayment() | Sets only one payment of type PurchasePaymentModel. Must have 'type' defined |
| payment | addPayment(*\WoowUp\Models\PurchasePaymentModel* $payment) | | Adds a payment of type PurchasePaymentModel |
| branch_name | setBranchName(*string* branch_name) | getBranchName() | |
| seller | setSeller(*\WoowUp\Models\SellerModel* $seller) | getSeller() | See SellerModel.md |
| createtime | setCreatetime(*string* $createtime) | getCreatetime() | Format: YYYY-MM-DD HH:ii:ss |
| approvedtime | setApprovedTime(*string* $approvedtime) | getApprovedTime() | Format: YYYY-MM-DD HH:ii:ss |
| metadata | setMetadata($metadata) | getMetadata() | |

## Validation

To have a valid Purchase the following fields must be defined:
+ invoice number
+ document, email and/or service_uid
+ purchase_detail
+ prices
+ branch_name
+ channel
+ createtime

Also, the following must be valid:
+ prices as PurchasePricesModel
+ payment as PurchasePaymentModel or array of PurchasePaymentModel
+ purchase_detail as array of PurchaseItemModel
+ (only if set) seller as SellerModel

## Example
```php
<?php

include '\WoowUp\Models\PurchaseModel';
include '\WoowUp\Models\PurchasePricesModel';
include '\WoowUp\Models\PurchasePaymentModel';
include '\WoowUp\Models\PurchaseItemModel';
include '\WoowUp\Models\SellerModel';

// Creating empty purchase
$purchase = new \WoowUp\Models\PurchaseModel;

// Setting invoice_number and customer's email
$purchase->setInvoiceNumber('P-001');
$purchase->setEmail('john.doe@example.com');

// Adding purchase detail (items)
$purchase->setPurchaseDetail(buildPurchaseDetail());

// Adding prices
$purchase->setPrices(buildPrices('1299.99', '299.99', '1000'));

// Setting channel
$purchase->setChannel('web');

// Setting branch name
$purchase->setBranchName('Ecommerce');

// Setting createtime
$purchase->setCreatetime(date('c'));

/*
 * Builds purchase prices
 */
function buildPrices($gross, $discount, $total)
{
    $prices = new \WoowUp\Models\PurchasePricesModel();
    $prices
        ->setGross($gross)
        ->setDiscount($discount)
        ->setTotal($total);
    
    return $prices;

/*
 * Builds and returns two arbitrary purchase items
 */
function buildPurchaseDetail()
{
    return array(
        buildItem('JCK-001-234', 'Winter Jacket 001', 1, 999.99, 'XL', 'Black'),
        buildItem('TSH-007-567', 'T-shirt 007', 2, 300.00, 'L', 'Blue'),
    );
}

/*
 * Builds an item
 */
function buildItem($sku, $name, $quantity, $unitPrice, $size, $color)
{
    // Creating empty purchase item
    $item = new \WoowUp\Models\PurchaseItemModel();
    
    // Setting all the fields
    $item
        ->setSku($sku)
        ->setProductName($name)
        ->setQuantity($quantity)
        ->setUnitPrice($unitPrice)
        ->setVariations([[
            'name'  => 'Size',
            'value' => $size,
        ], [
            'name'  => 'Color',
            'value' => $color,
        ]]);
        
    return $item;
}
