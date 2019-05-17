Represents the Category of a [Product](ProductModel.md)

## List of methods

| Field in API | Setter in php-client-v2 | Getter in php-client-v2 | Comments |
| --- | --- | --- | --- |
| id | setId(*string* $id) | getId() | |
| name | setName(*string* $name) | getName() | |
| url | setUrl(*string* $url) | getUrl() | |
| image_url | setImageUrl(*string* $image_url) | getImageUrl() | |

## Validation

To have a valid Category the following fields must be defined:
+ id
+ name

### Example
```php
<?php

include '\WoowUpV2\Models\CategoryModel';

// Creating empty category
$category = new \WoowUpV2\Models\CategoryModel();

// Setting id and name
$category->setId('JCK');
$category->setName('Jackets');

// Validation should return true
var_dump($category->validate());

// Setting URLs
$category->setUrl('http://my-store.example.com/jackets.html');
$category->setImageUrl('http://my-store.example.com/images/jackets.jpg');
```
