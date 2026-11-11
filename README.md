# Small E-commerce Management

A small multi-shop e-commerce system built with **Laravel 13**, **React 19 (Inertia.js)** and **MySQL**.

- **Admins** manage shops, shop owner accounts and a shared product catalog.
- **Shop owners** pick products from the shared catalog (or add their own) and sell them in their shops with their own price, details and thumbnail.
- **Customers** browse a public storefront that only shows products assigned to active shops.

## Contents

- [Features](#features)
- [Tech stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Demo accounts](#demo-accounts)
- [Try it out](#try-it-out)
- [Running with Docker (optional)](#running-with-docker-optional)
- [Data model](#data-model)
- [Design decisions](#design-decisions)
- [Running tests](#running-tests)
- [Project structure](#project-structure)

## Features

### Admin panel (`/admin`)

- Log in as an admin (public registration is disabled).
- Create, edit, search and delete **shop owner** accounts. An owner who still has shops cannot be deleted.
- Create, edit, search and delete **shops**, assign each to an owner, and mark them active or inactive.
- Create, edit, search and delete **catalog products** with a name, description, suggested price and thumbnail.

### Shop owner panel (`/owner`)

- See only their own shops.
- Browse the **shared catalog**: products created by admins and by any shop owner.
- **Add a catalog product to a shop** with a shop-specific price, details and thumbnail. Details and thumbnail are optional; without them the catalog values are shown.
- Edit or remove products in their shops. Removing a product from a shop does not remove it from the catalog.
- **Create their own products.** They join the shared catalog, so other shops can sell them too. Owners can edit the products they created.

### Storefront (`/`)

- Customers can browse without an account.
- Lists active shops and their products, with search across all shops or within one shop.
- Product pages show the shop's own price, details and thumbnail, plus other shops selling the same product and their prices.
- **Products not assigned to any shop are never visible**, and inactive shops are hidden (their URLs return 404).

## Tech stack

| Layer           | Tools                                                                           |
| --------------- | ------------------------------------------------------------------------------- |
| Backend         | Laravel 13, PHP 8.4, Laravel Fortify (authentication)                           |
| Frontend        | React 19, TypeScript, Inertia.js v3, Tailwind CSS 4, shadcn/ui components       |
| Routing helpers | Laravel Wayfinder (type-safe routes in TypeScript, generated at build time)     |
| Database        | MySQL (tests use in-memory SQLite)                                              |
| Quality         | Pest tests, PHPStan (Larastan), Pint, oxlint/oxfmt via Vite+, GitHub Actions CI |

## Requirements

- PHP **8.4+** with the usual Laravel extensions (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `ctype`, `bcmath`)
- Composer 2
- Node.js **22.18+** or **24.11+**, with npm
- MySQL **8.0+** (MariaDB 10.6+ also works)

## Installation

**1. Clone the repository**

```bash
git clone https://github.com/jahid-cuet/ecommerce-management.git
cd ecommerce-management
```

**2. Install PHP dependencies and create the environment file**

```bash
composer install
cp .env.example .env
php artisan key:generate
```

**3. Create the database**

```bash
mysql -u root -p -e "CREATE DATABASE ecommerce_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Then set your MySQL username and password in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_management
DB_USERNAME=root
DB_PASSWORD=your-password
```

**4. Run the migrations with demo data, and link the storage folder**

```bash
php artisan migrate --seed
php artisan storage:link
```

`storage:link` makes uploaded product images publicly accessible. Without it, thumbnails will not load.

**5. Install and build the frontend**

```bash
npm install
npm run build
```

**6. Start the app**

```bash
php artisan serve
```

Open **http://localhost:8000**.

> **Shortcut:** after creating the database (step 3), copying `.env.example` to `.env` and setting your database password, `composer setup` runs steps 2, 4 and 5 for you.

### Development mode (optional)

To get hot reloading while editing the frontend, run the Laravel server and Vite in two terminals:

```bash
php artisan serve
npm run dev
```

`composer dev` starts both, plus a queue worker and a log viewer, in one terminal (it needs the `pcntl` extension, so it does not work on Windows).

### Reset the demo data

```bash
php artisan migrate:fresh --seed
```

## Demo accounts

All demo passwords are `password`.

| Role       | Email                | What they have                                                      |
| ---------- | -------------------- | ------------------------------------------------------------------- |
| Admin      | `admin@example.com`  | Full admin panel                                                    |
| Shop owner | `owner@example.com`  | Rahim Ahmed: **Gadget Hub** (active) and **Book Corner** (inactive) |
| Shop owner | `owner2@example.com` | Karim Hossain: **Daily Bazaar** (active)                            |

The demo catalog has 11 products. The seed data covers each rule in the assignment:

- **Same product, different prices:** Wireless Mouse costs ৳1,150 at Gadget Hub and ৳1,250 at Daily Bazaar.
- **Shop-specific details:** Gadget Hub's Wireless Mouse and Daily Bazaar's Coffee Mug have their own descriptions.
- **Shop-specific thumbnails:** Gadget Hub's Bluetooth Headphones and Daily Bazaar's Cotton T-Shirt have their own images.
- **Products created by owners:** Power Bank (Rahim) and Jute Bag and Notebook Set (Karim) are in the shared catalog.
- **Unassigned products:** Steel Water Bottle and Notebook Set are in the catalog but sold by no shop, so customers never see them.
- **Inactive shop:** Book Corner lists a product but is inactive, so it is hidden from customers.

## Try it out

1. **As a customer:** open http://localhost:8000. Browse Gadget Hub and Daily Bazaar and open Wireless Mouse to compare the two shops' prices. Search for "water bottle": nothing is found, because no shop sells it.
2. **As a shop owner:** log in as `owner@example.com` and open **My shops → Gadget Hub → Add from catalog**. Add Steel Water Bottle with your own price. It now appears in the storefront. Try **New catalog product** to add a product of your own.
3. **As an admin:** log in as `admin@example.com`. Create a shop owner, give them a shop, and manage the catalog.

## Running with Docker (optional)

If you prefer not to install PHP, Composer, Node.js and MySQL, you can run the project with [Docker](https://docs.docker.com/get-docker/) instead of following the installation steps above.

```bash
git clone https://github.com/jahid-cuet/ecommerce-management.git
cd ecommerce-management
docker compose up --build
```

The first build takes a few minutes. When the log shows `Ready on http://localhost:8000`, open **http://localhost:8000** and log in with one of the [demo accounts](#demo-accounts).

On first start, the container sets up everything automatically: it creates `.env` and the app key, waits for MySQL, runs the migrations, seeds the demo data and links the storage folder. Later starts keep your data and do not seed again.

| Task                                          | Command                                                                      |
| --------------------------------------------- | ---------------------------------------------------------------------------- |
| Run in the background                         | `docker compose up -d --build`                                               |
| Stop                                          | `docker compose down`                                                        |
| Reset all data (database and uploaded images) | `docker compose down -v`, then `docker compose up`                           |
| Use another port if 8000 is taken             | `APP_PORT=8080 docker compose up`                                            |
| Run the tests inside the container            | `docker compose exec app php artisan test`                                   |
| Open a MySQL shell                            | `docker compose exec mysql mysql -u ecommerce -psecret ecommerce_management` |

MySQL runs only inside Docker and is not published on your machine, so it does not clash with a MySQL server you already have on port 3306.

## Data model

```
users ──< shops ──< listings >── products
  │                                  │
  └──────────── created_by ──────────┘
```

| Table      | Purpose                                                                                                                                                          |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `users`    | Admins and shop owners, distinguished by a `role` column (`admin` / `shop_owner`).                                                                               |
| `shops`    | Each shop belongs to one owner. `is_active` controls whether customers can see it.                                                                               |
| `products` | The **shared catalog**. `price` is a suggested price. `created_by` records who added it (an admin or an owner) and is kept as `NULL` if that account is deleted. |
| `listings` | Assigns a catalog product to a shop, with the shop's own `price` and optional `description` and `thumbnail_path`. Unique per `(shop_id, product_id)`.            |

## Design decisions

- **Listings as a first-class model.** The shop-specific price, details and thumbnail live in a `listings` table, not on the product. The catalog stays shared while every shop controls what its customers see. The storefront reads only through listings, so an unassigned product cannot leak.
- **Fallbacks instead of copies.** A listing's details and thumbnail are optional. When empty, the catalog values are shown, so catalog improvements reach every shop that hasn't overridden them.
- **Authorization in layers.**
    - A `role` middleware separates the admin and owner panels.
    - `ShopPolicy` limits owners to their own shops.
    - Listing URLs are scoped to their shop, so `/owner/shops/1/listings/5` cannot reach a listing of another shop.
    - `ProductPolicy` lets owners edit only products they created.
- **Accounts are created by admins.** Public registration is disabled, and `role` is not mass-assignable.
- **Image cleanup.** Thumbnails are deleted when they are replaced or removed, and when their product, listing or shop is deleted.
- **Money** is stored as `DECIMAL(10,2)`, never as a float.
- **Shop and product URLs** use slugs that stay the same when the name changes.

## Running tests

Tests use an in-memory SQLite database, so they don't need MySQL or built frontend assets.

```bash
php artisan test
```

To run everything CI runs (formatting, linting, TypeScript, PHPStan and tests):

```bash
composer ci:check
```

The test suite covers role access, admin CRUD, shop ownership rules, listing scoping, validation, thumbnail handling, storefront visibility rules and the demo seeder.

## Project structure

```
app/
  Enums/UserRole.php                 Roles and where each one lands after login
  Http/Controllers/Admin/            Admin panel: shop owners, shops, products, dashboard
  Http/Controllers/Owner/            Owner panel: shops, listings, catalog, own products
  Http/Controllers/Store/            Public storefront
  Http/Middleware/EnsureUserHasRole.php
  Models/                            User, Shop, Product, Listing
  Policies/                          ShopPolicy, ProductPolicy
  Support/Thumbnail.php              Thumbnail storage helper
database/
  migrations/                        Users (with role), shops, products, listings
  seeders/                           Demo users, catalog, shops and listings
resources/js/
  pages/admin/  pages/owner/  pages/store/
  components/                        Shared forms, tables and storefront cards
routes/
  web.php  admin.php  owner.php
tests/Feature/                       Pest feature tests
```
