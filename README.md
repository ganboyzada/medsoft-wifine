# Medsoft WiFine

Secure multi-organization Laravel platform for guest WiFi captive portals.

## What It Does

- Multi-tenant architecture: each organization has isolated portals, surveys, guests, campaigns, and analytics.
- Captive portal onboarding flow: guest fills identity fields + custom satisfaction survey before internet access.
- Custom per-organization branding: logo, primary/accent colors, portal-specific welcome and terms text.
- Dynamic survey builder: question types (`short_text`, `long_text`, `single_choice`, `multi_choice`, `rating`, `nps`, `yes_no`, `phone`, `date`).
- Superadmin provisioning: create new organization + org admin + default portal and survey in one form.
- Gateway-ready integration API: signed endpoints for session open/status/authorize for network equipment.
- Sellable add-ons included: post-login campaign cards, CSV exports, tenant analytics dashboard, audit trail.
- Localization support: Azerbaijani (`az`) default, with per-organization switch to English (`en`).

## Roles

- `superadmin`: full platform management.
- `org_admin`: full control inside one organization.
- `org_analyst`: reserved role constant for future read-only policy expansion.

## Stack

- Laravel 10
- PHP 8.1+
- MySQL/PostgreSQL/SQLite (default migrations support all)
- Blade UI (no frontend build step required for core flows)

## Quick Start

1. Install dependencies:
```bash
composer install
```

2. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Set DB credentials in `.env`, then:
```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

4. Demo users after seeding:
- Superadmin: `superadmin@medsoft.local` / `ChangeMe123!`
- Demo org admin: `admin@democafe.local` / `ChangeMe123!`

## Core URL Map

- Admin login: `/login`
- Guest portal: `/portal/{portal-slug}`
- Superadmin organizations: `/superadmin/organizations`
- Organization dashboard: `/organization/dashboard`

## Captive Gateway Integration

All gateway API routes are under `/api/gateway` and require HMAC headers:

- `X-Portal-Key`: portal integration key
- `X-Timestamp`: unix timestamp (seconds)
- `X-Signature`: `sha256_hmac(secret, "{timestamp}.{METHOD}.{PATH}.{RAW_BODY}")`

Timestamp must be within 5 minutes.

### 1) Open Session

`POST /api/gateway/sessions/open`

Body example:
```json
{
  "client_mac": "AA:BB:CC:DD:EE:FF",
  "ap_mac": "11:22:33:44:55:66",
  "ip_address": "10.10.1.23",
  "redirect_url": "https://example.com/after-login",
  "metadata": {
    "ssid": "GuestWiFi"
  }
}
```

Response includes `landing_url` for redirecting the user device to the captive form.

### 2) Poll Session Status

`GET /api/gateway/sessions/{session_token}`

Use this to decide whether WiFi can be granted:
- `grant_wifi = true` when status is `survey_completed` or `authorized`.

### 3) Mark Authorized

`POST /api/gateway/sessions/{session_token}/authorize`

Call after your network controller has actually granted internet access.

## Suggested Device Flow

1. Device tries internet -> gateway intercepts.
2. Gateway calls `open` -> receives `landing_url`.
3. Gateway redirects guest phone browser to `landing_url`.
4. Guest submits profile + survey.
5. Gateway polls `status` until `grant_wifi = true`.
6. Gateway grants access and optionally calls `authorize`.
7. Gateway redirects to returned `redirect_url` if needed.

This approach works across Mikrotik, UniFi, Aruba, Cisco, or custom controller glue.

## Security Highlights

- Role middleware for admin isolation.
- Tenant ownership checks in organization controllers.
- Signed API integration for network device callbacks.
- Server-side validation for all dynamic question answers.
- CSRF protection on web forms and Laravel throttling on public endpoints.
- Encrypted-at-rest storage for portal integration secret.
- Audit log events for sensitive admin operations.

## Feature Extension Ideas

- OTP verification before granting session.
- WhatsApp/SMS campaigns based on NPS segment.
- Multi-language captive portal renderer.
- Device vendor adapters (`app/Services/Gateways/*`) to directly push authorization.
- BI pipeline using queue jobs and a data warehouse sink.

## Testing

Run:
```bash
php artisan test
```

If using SQLite for CI/local speed, set:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```
