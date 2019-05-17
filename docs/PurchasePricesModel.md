Represents the prices in a purchase

## List of methods

| Field in API | Setter in php-client-v2 | Getter in php-client-v2 | Comments |
| --- | --- | --- | --- |
| cost | setCost(*float* $cost) | getCost() | |
| shipping | setShipping(*float* $shipping) | getShipping() | |
| gross | setGross(*float* $gross) | getGross() | |
| tax | setTax(*float* $tax) | getTax() | |
| discount | setDiscount(*float* $discount) | getDiscount() | |
| total | setTotal(*float* $total) | getTotal() | |

## Validation

To have a valid PurchasePrices the following fields must be defined:
+ total

## Example
```php
<?php

include '\WoowUpV2\Models\PurchasePricesModel';

// Creating empty purchase prices
$prices = new \WoowUpV2\Models\PurchasePricesModel();

// Setting total
$prices->setTotal(900.0);

// Validation should already return true
var_dump($prices->validate());

// Setting gross and discount
$prices->setGross(1000.0);
$prices->setDiscount(100.0);
