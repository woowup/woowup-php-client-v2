Represents the seller of a purchase

## List of methods

| Field in API | Setter in php-client-v2 | Getter in php-client-v2 | Comments |
| --- | --- | --- | --- |
| name | setName(*string* $name) | getName() | |
| email | setEmail(*string* $email) | getEmail() | |
| external_id | setExternalId(*string* external_id) | getExternalId() | |

## Validation

To have a valid Seller the following fields must be defined:
+ name
+ email

## Example
```php
<?php

include '\WoowUpV2\Models\PurchaseModel';
include '\WoowUpV2\Models\SellerModel';

// Creating empty seller
$seller = new \WoowUpV2\Models\SellerModel();

// Setting name and email
$seller->setName('John Doe');
$seller->setEmail('john.doe@example.com');

// Validation should return true
var_dump($seller->validate());

// Creating empty purchase
$purchase = new \WoowUpV2\Models\PurchaseModel();

// Adding seller to the purchase
$purchase->setSeller($seller);
