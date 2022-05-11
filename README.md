## About Eshop backend api

Eshop is a backend api for an E-commerce website

-   Categories
-   Products
-   Orders
-   Login + Registration
-   Unit Test and Feature Test

## Eshop Setup

-   Clone the repo
-   Go into the repo
-   run below commands in terminal

```sh
composer install
php artisan key:generate
cpy .env.example .env
```

-   Create database
-   Put Database info inside .env file
-   Run the below commands in your terminal

```sh
php artisan migrate
php artisan db:seed
```
