Represents a product

Before checking this model out, it is recommended to take a look at the following:
+ [CategoryModel documentation](CategoryModel.md)

## List of methods

| Field in API | Setter in php-client-v2 | Getter in php-client-v2 | Comments |
| --- | --- | --- | --- |
| sku | setSku(*string* $sku) | getSku() | |
| name | setName(*string* $name) | getName() | |
| base_name | setBaseName(*string* $base_name) | getBaseName() | Additional name for campaigns purpose |
| brand | setBrand(*string* $brand) | getBrand() | |
| description | setDescription(*string* $description) | getDescription() | |
| url | setUrl(*string* $url) | getUrl() | |
| image_url | setImageUrl(*string* $image_url) | getImageUrl() | |
| price | setPrice(*float* $price) | getPrice() | |
| offer_price | setOfferPrice(*float* $offer_price) | getOfferPrice() | Special-offer price |
| stock | setStock(*int* $stock) | getStock() | |
| available | setAvailable(*boolean* $available) | getAvailable() | |
| category | setCategory(*array* $category)<br>addCategory(*\WoowUp\Models\CategoryModel* $category) | getCategory() | |
| specifications | setSpecifications(*array* $specifications) | getSpecifications() | |
| metadata | setMetadata($metadata) | getMetadata() | |
| id | *not available* | getId() | WoowUp's product-id |
| createtime | *not available* | getCreatetime() | |
| updatetime | *not available* | getUpdatetime() | |

## Validation

To have a valid Product the following fields must be defined:
+ sku
+ name

### Example
```php
<?php

include '\WoowUp\Models\ProductModel';
include '\WoowUp\Models\CategoryModel';

// Creating empty product
$product = new \WoowUp\Models\ProductModel();

// Setting SKU and name
$product->setSku('JCK-001-234');
$product->setName('Winter Jacket 001');

// Validation should return true
var_dump($product->validate());

// Creating a category and a subcategory Jackets -> Winter
$category = new \WoowUp\Models\CategoryModel();
$category->setId('JCK');
$category->setName('Jackets');

$subcategory = new \WoowUp\Models\CategoryModel();
$subcategory->setId('WNT');
$subcategory->setName('Winter');


// Adding category
// Option 1
$product->addCategory($category);
$product->addCategory($subcategory);
// Option 2
$product->setCategory(array($category, $subcategory));
```
