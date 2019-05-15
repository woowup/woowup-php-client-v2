Represents the payments of a purchase

## List of methods

| Field in API | Setter in php-client-v2 | Getter in php-client-v2 | Comments |
| --- | --- | --- | --- |
| type | setType(*string* $type) | getType() | Valid values: 'credit', 'debit', 'mercadopago', 'todopago', 'cash', 'other'. |
| brand | setBrand(*string* $brand) | getBrand() | Example: 'VISA' |
| bank | setBank(*string* $bank) | getBank() | Example: 'Bank of America' |
| total | setTotal(*float* $total) | getTotal() | Total paid by the customer with this payment method |
| installments | setInstallments(*int* $installments) | getInstallments() | |

## Validation

To have a valid PurchasePayment the following fields must be defined:
+ type


## Example
```php
<?php

include '\WoowUp\Models\PurchasePaymentModel';

// Creating empty purchase payment
$payment = new \WoowUp\Models\PurchasePayment();

// Setting type
$payment->setType('credit');

// Validation should return true
var_dump($payment->validate());

// Setting brand, bank, total and installments
$payment->setBrand('VISA');
$payment->setBank('Bank of America');
$payment->setTotal(900.00);
$payment->setInstallments(6);
