# Bawaskar ERP — Fastkart Laravel Admin + Storefront + Mobile APIs

A Laravel ERP/eCommerce backend for:

- **B2B:** Dealer → Assigned Salesman → Admin → Dispatch
- **B2C:** Customer → Admin → Dispatch
- **Salesman HRMS:** attendance, GPS check-in/check-out, dealer visits, leave, expenses, salary, targets, commission, tour plans and assets

## Design integration

- The previous admin design assets have been removed.
- The admin panel now uses the supplied **Fastkart back-end design**.
- Existing controllers, models, database migrations, APIs and separate admin CRUD views are preserved.
- The public storefront uses **Fastkart front-end index-5** as the home page.
- Every supplied Fastkart front-end HTML page has been converted into a separate Laravel Blade view.
- All three Fastkart invoice designs are available from Sale Order, Proforma and Invoice print links using `?template=1`, `?template=2` or `?template=3`.
- All five Fastkart email templates are available under **Admin → System → Email Templates**.

## Installation

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

Admin URL: `http://localhost:8000/admin/login`
Storefront URL: `http://localhost:8000/`

Use the administrator account already present in your database. The intended local account is:

- Email: `admin@turnkeyinfotech.com`
- Password: `123456`
- Role: `admin`

Passwords must remain hashed in the database.

## API structure

The existing mobile APIs remain unchanged under `/api`. They are intended for the future Customer, Dealer and Salesman Flutter apps.

## Important production work

External integrations still require real credentials and business rules: payment gateway, OTP/SMS/WhatsApp provider, courier tracking, FCM, SMTP and statutory payroll.
