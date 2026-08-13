# Changelog

All notable changes to this project are documented in this file. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this
project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-08-09

### Added

- Unified multi-gateway package for PHP 8.4+ and Laravel 13/14.
- `CountryCatalog` covering all 195 countries with a curated per-country
  "top" list of 4-5 gateways spanning mobile money, card, bank transfer and
  virtual card rails.
- Generated gateway catalog (`src/Data/gateways_by_country.php`) with 1982+
  unique gateway keys and 27,300+ country entries via the
  `scripts/catalog/` generator (master gateway database + country pool +
  concrete-driver mapping).
- 60+ concrete gateway drivers plus generic redirect/API/mobile/bank/card/
  crypto/wallet/virtual-card drivers.
- Laravel integration: service provider, facades, config, helpers, contracts,
  models, migrations, events, jobs, middleware, controllers, Blade views,
  notifications, reports (JSON/CSV/Excel/PDF), QR codes, invoices, AI-agent
  analysis, and an admin panel.
- Complete test suite: PHPUnit 175 tests / 4667 assertions, PHPStan level 5
  zero errors, PHPCS PSR-12 zero errors, `php -l` clean, `composer validate`
  valid.

### Changed

- Namespace unified to `Hadi\Payment` for all source code.
- `getAvailableGateways()` is now driven by `GatewayFactory` rather than a
  hard-coded list.
- QR code, invoice, cache, and event-listener services registered as
  singletons in `HadiPaymentServiceProvider`.

### Fixed

- `InvoiceService::generateInvoicePdf()` now renders a real PDF via
  `SimplePdfWriter`.
- `InvoiceItem` calculated NOT NULL columns auto-filled on create.
- `PaymentValidator::sanitizePaymentData()` no longer clobbers amount/
  currency normalization and keeps `orderId`/`callbackUrl`.
- `PaymentProblem::markAsResolved()` accepts a nullable resolver id.
- Payments migration gains `product_name`, `customer_data`, `qr_code_path`,
  `completed_at`, `failed_at`.
- `QRCodeService` returns a real string and uses config-driven routes guarded
  by `Route::has()`.
- `PaymentHistoryService` statistics use fresh queries per metric.
- `AIAgentService` merges amount/time/behavior anomalies.
- `PaymentGatewayService` normalizes camelCase/snake_case bidirectionally.
- `WebhookSignatureMiddleware` falls back to the global webhook secret.
- `PaymentEventListener` now sends `PaymentNotification` (mail + database) on
  payment completed/failed/refunded when `notifications.mail.recipient` is
  configured; config gains `invoice.prefix` and `defaults.system_user_id` keys.
- `AIAgentService` resolves the system user id from
  `hadi-payment.defaults.system_user_id`.
- Catalog concrete-driver depth: new `scripts/catalog/drivers.php` maps ~70
  gateway slugs to their concrete driver classes; the generator merges it into
  the master database and country pool so catalog entries and per-country top
  lists resolve to concrete drivers instead of generic ones.
- Async event/job/notification chain is now reachable from the sync flow: new
  `PaymentRecordingService` (enabled via `hadi-payment.events.enabled`, default
  off) persists a `Payment` model and dispatches `PaymentInitialized`,
  `PaymentCompleted`, `PaymentFailed` and `PaymentRefunded` from
  `PaymentGatewayService`, flowing into `PaymentEventListener` and
  `PaymentNotification`; the sync flow stays stateless when disabled.

### Dedication

Dedicated to Sharif Osman Bin Hadi.
