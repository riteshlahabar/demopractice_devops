# Bawaskar ERP API Reference

Preferred base URL: `https://your-domain.example/api/v1`

Legacy `/api` aliases are retained for compatibility.

Send protected requests with:

```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

## Authentication

- `POST /auth/otp/request`
- `POST /auth/customer/otp/verify`
- `POST /auth/customer/register`
- `POST /auth/customer/login`
- `POST /auth/dealer/otp/verify`
- `POST /auth/dealer/login`
- `POST /auth/salesman/login`
- `POST /auth/admin/login`
- `POST /auth/logout`

## Common catalog

- `GET /catalog/categories`
- `GET /catalog/products?audience=customer|dealer`
- `GET /translations?locale=mr`

## Customer app

- `GET /customer/dashboard`
- `GET /customer/profile`
- `POST /customer/addresses`
- `POST /customer/support`
- `GET /customer/orders`
- `POST /customer/orders`
- `GET /customer/orders/{order}`

Customer orders go directly to admin review.

## Dealer app

- `GET /dealer/dashboard`
- `GET /dealer/profile`
- `POST /dealer/addresses`
- `POST /dealer/support`
- `GET /dealer/statements`
- `GET /dealer/orders`
- `POST /dealer/orders`
- `GET /dealer/orders/{order}`

Dealer orders go to the assigned salesman before admin review.

## Salesman app

- `GET /salesman/dashboard`
- `GET /salesman/dealers`
- `POST /salesman/attendance/check-in`
- `POST /salesman/attendance/check-out`
- `GET|POST /salesman/visits`
- `GET|POST /salesman/orders`
- `POST /salesman/orders/{order}/forward-to-admin`
- `POST /salesman/collections`
- `GET|POST /salesman/expenses`
- `GET|POST /salesman/leaves`
- `GET /salesman/assets`
- `GET /salesman/salary`
- `GET /salesman/targets`
- `GET /salesman/tour-plans`
- `GET /salesman/deliveries`

## Admin API

- `GET /admin/dashboard`
- `POST /admin/salesmen`
- `GET /admin/dealers`
- `POST /admin/dealers/{dealer}/approve`
- `POST /admin/dealers/{dealer}/assign`
- `POST /admin/products`
- `POST /admin/orders/{order}/status`
- `POST /admin/orders/{order}/dispatch`
- `POST /admin/salesmen/{salesman}/assets`
- `POST /admin/translations`

## Order item request example

```json
{
  "items": [
    {"product_id": 1, "quantity": 2}
  ],
  "notes": "Deliver during business hours"
}
```
