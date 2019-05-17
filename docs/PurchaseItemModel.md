Represents an item in a purchase

## List of methods

PurchaseItemModel extends [ProductModel](ProductModel.md). The additional methods regard product's quantity, unit price, variations and warranties.

| Field in API | Setter in php-client-v2 | Getter in php-client-v2 | Comments |
| --- | --- | --- | --- |
| product_name | setProductName(*string* $product_name) | getProductName() | Also sets the field 'name' |
| quantity | setQuantity(*int* $quantity) | getQuantity() | |
| unit_price | setUnitPrice(*float* $unit_price) | getUnitPrice() | |
| variations | setVariations(*array* $variations) | getVariations() | Item variations (e.g. size) |
| manufacturer_warranty_date | setManufacturerWarrantyDate(*string* $manufacturer_warranty_date) | getManufacturerWarrantyDate() | Format: YYYY-MM-DD |
| extension_warranty_date | setExtensionWarrantyDate(*string* $extension_warranty_date) | getExtensionWarrantyDate() | Format: YYYY-MM-DD |
| with_extension_warranty | setWithExtensionWarranty(*boolean* $with_extension_warranty) | getWithExtensionWarranty() | |

## Validation

To have a valid PurchaseItem the following fields must be defined:
+ sku
+ product_name
+ quantity
+ unit_price


## Example
```php
<?php

include '\WoowUp\Models\PurchaseItemModel';

// Creating empty purchase item
$item = new \WoowUp\Models\PurchaseItemModel();

// Setting SKU and name
$item->setSku('JCK-001-234');
$item->setProductName('Winter Jacket 001');

// Setting unit_price and quantity
$item->setQuantity(1);
$item->setUnitPrice(999.99);

// Validation should return true
var_dump($item->validate());

// Setting variations
$variations = [[
    'name'  => 'Size',
    'value' => 'XL',
  ], [
    'name'  => 'Color',
    'value' => 'Black',
]];
$item->setVariations($variations);
