Represents the customer of a purchase sent to `POST /purchases/with-customer` and `POST /purchases/bulk/with-customer`. The identifiers are sent clean in the root of `customer`, and what the seller typed goes in `customer.raw`, along with what the cleanser did with each value.

## List of methods

| Field in API | Setter in php-client-v2 | Getter in php-client-v2 | Comments |
| --- | --- | --- | --- |
| document | setDocument(*string* $document) | getDocument() | Also stored in `raw.document` |
| service_uid | setServiceUid(*string* $service_uid) | getServiceUid() | Also stored in `raw.service_uid` |
| telephone | setTelephone(*string* $telephone, *bool* $sanitize = true) | getTelephone() | Same signature as PurchaseModel/UserModel, but sanitizes by default. Sets `raw.telephone` and, when sanitizing, `raw.telephone_status` |
| email | setEmail(*string* $email, *bool* $sanitize = true, *bool* $disableIdNoemail = false) | getEmail() | Same signature as PurchaseModel/UserModel. Sets `raw.email` and, when sanitizing, `raw.email_status` |
| raw | | getRaw(), getEmailStatus(), getTelephoneStatus() | |

## Statuses

| Status | Email | Telephone |
| --- | --- | --- |
| validated | The cleanser left it unchanged | The cleanser left it unchanged |
| cleaned | The cleanser changed it, even only case or spaces | The cleanser reformatted it |
| generic | It already came as `@noemail.com` or a `@mail.mercadolibre.com` proxy | |
| rejected | It has no valid format: `<document\|service_uid\|telephone>@noemail.com` is sent instead | The cleanser dropped it: it is not sent in `customer` |

Set the document, service_uid or telephone **before** the email, so a rejected email can be replaced by `<id>@noemail.com`.

## Validation

To have a valid customer at least one of the following fields must be defined:
+ service_uid
+ document
+ email
+ telephone

## Example
```php
<?php

$customer = new \WoowUpV2\Models\PurchaseCustomerModel();
$customer->setDocument('30111222');
$customer->setTelephone('+57 301 152 8226'); // cleaned
$customer->setEmail('Ana.Perez@Gmail.com');  // cleaned

$purchase->setCustomer($customer);

$payload = $woowup->purchases->createWithCustomer($purchase);
// $payload->userapp_id, $payload->customer_created
```

`createWithCustomer()` throws a `GuzzleHttp\Exception\RequestException` on `bad_request`, `error_branch_name`, `duplicated_purchase_number` and `customer_not_saved`; the code is in the response body. `bulkCreateWithCustomer()` returns the payload on `200` and on a partial `207`, where the sales that did not go in come in `errors`.
