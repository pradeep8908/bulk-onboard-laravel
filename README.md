# Bulk Organization Onboarding API

A Laravel 12 REST API that accepts bulk organization onboarding requests (up to 1000 records),
responds quickly with a `batch_id`, and processes each organization asynchronously using queues.

---

## 🚀 Tech Stack

- Laravel 12
- PHP 8.3+
- MySQL
- Queue: Database (default) or Redis (optional)
- PHPUnit for testing

---

## 📦 Features

- Bulk onboarding (1–1000 organizations per request)
- Fast API response (`202 Accepted`)
- Background job processing
- Idempotent job execution
- Batch status tracking
- Rate limiting (10 RPS)
- Fully tested (unit + feature tests)

---

## 🛠 Setup Instructions

### 1️⃣ Clone & Install
```bash
composer install
cp .env.example .env
php artisan key:generate


## Sample APIs
# For bulk-onboard
  curl --location 'http://localhost:8000/api/bulk-onboard' \
--header 'Content-Type: application/json' \
--data-raw '{
  "organizations": [
    {
      "name": "Acme Corp",
      "domain": "acme.com",
      "contact_email": "admin@acme.com"
    },
    {
      "name": "Beta Corp",
      "domain": "beta.com",
      "contact_email": "admin@beta.com"
    },
    {
      "name": "Gamma Corp",
      "domain": "gamma.com",
      "contact_email": "admin@gamma.com"
    }
  ]
}
'

-------------------------
# To get details
curl --location 'http://localhost:8000/api/batch/b07b65aa-c7e0-4b94-a6f4-ddd05c9cb6ed' \
--data ''