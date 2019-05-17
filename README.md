# WoowUp PHP

## Installation

You can install **woowup-php-client-v2** via composer or by downloading the source.

#### Via Composer:

**woowup-php-client-v2** is available on Packagist as the
[`woowup/woowup-php-client-v2`](https://packagist.org/packages/woowup/woowup-php-client-v2) package.

## Models and methods

In order to avoid common mistakes (e.g. orders uploaded without a `branch_name` or mistaking the fields' names like calling ~~create_time~~ the actual order's `createtime`) the **new php-client has every entity modeled**. This means that every entity will be an instance of a **Model class** where every attribute is private, so developers will use setters and getters to handle them. Additionally, every entity will be validated before attempting to create or update it into WoowUp. In other words, it will detect mistakes before doing any request to WoowUp's API saving time for developers.

[UserModel documentation](docs/UserModel.md)<br>
[ProductModel documentation](docs/ProductModel.md)<br>
[PurchaseModel documentation](docs/PurchaseModel.md)<br>
[CategoryModel documentation](docs/CategoryModel.md)<br>
[PurchaseItemModel documentation](docs/PurchaseItemModel.md)<br>
[PurchasePricesModel documentation](docs/PurchasePricesModel.md)<br>
[PurchasePaymentModel documentation](docs/PurchasePaymentModel.md)<br>
[SellerModel documentation](docs/SellerModel.md)

## Validation

Every Model has its own validation method called `validate()`. This method checks if the model suits the API schemas before attempting an insertion/update.

## Example

Inside `/examples` there is an example called `import_from_csv.php`. It imports customers and sales read from the file `ventas.csv`.

## API documentation

The documentation for the WoowUp API is located [here](https://docs.woowup.com).

## Prerequisites

* PHP >= 5.3
* The PHP JSON extension
* PHP Internationalisation module (phpX.X-intl)
