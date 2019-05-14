## List of methods

| Field in API | Setter in php-client-v2 | Getter in php-client-v2 | Comments |
| --- | --- | --- | --- |
| id | setId(*string* $id) | getId() | |
| name | setName(*string* $name) | getName() | |
| url | setUrl(*string* $url) | getUrl() | |
| image_url | setImageUrl(*string* $image_url) | getImageUrl() | |

### Example
```php
<?php

include '\WoowUp\Models\CategoryModel';

// Creating empty category
$category = new \WoowUp\Models\CategoryModel();

// Setting values
$category->setId('JCK');
$category->setName('Jackets');
$category->setUrl('http://my-store.example.com/jackets.html');
$category->setImageUrl('http://my-store.example.com/images/jackets.jpg');
```
