# Hadi Payment

**Inspired by the spirit and ideals of Sharif Osman Bin Hadi, Hadi Payment is built with a vision of connection, accessibility, and digital empowerment.**

Hadi Payment is a modern global payment infrastructure designed to make digital transactions simple, secure, and accessible across borders. With support for **195+ countries** and integration with **3,000+ payment gateways**, businesses can connect with customers around the world through one unified payment platform.

From local payment methods to international transactions, Hadi Payment brings flexibility, reliability, and a seamless checkout experience together in one ecosystem.

**195+ Countries · 3,000+ Payment Gateways · One Global Payment Network**

*Inspired by the values of Sharif Osman Bin Hadi — built for a more connected digital world.*

---

## About

Hadi Payment is a multi-gateway payment package for **PHP 8.4+** and **Laravel 13/14**. It exposes a single, consistent API — `initialize → process → verify → refund` — across every supported gateway, so your application code never changes when you add a new provider.

- **195 countries** in the built-in catalog, each with a curated top list.
- **27,000+ catalog entries** spanning mobile money, cards, bank transfer, wallets, crypto and virtual cards (1,900+ unique gateway keys).
- **~70 concrete driver classes** for the most popular gateways, with config-driven generic flows for the long tail.
- Dedicated **Bangladesh rail**: bKash, Nagad, Rocket, SSLCommerz, ShurjoPay, AamarPay, SureCash, uPay, Upay, **BanglaQR**, and more.

## Quick Start

### Install

```bash
composer require hadi/hadi-payment
```

Publish the config (Laravel):

```bash
php artisan vendor:publish --provider="Hadi\Payment\HadiPaymentServiceProvider"
```

### Usage

```php
use Hadi\Payment\PaymentProcessor;

$processor = PaymentProcessor::gateway('bkash', [
    'appKey'    => env('BKASH_APP_KEY'),
    'appSecret' => env('BKASH_APP_SECRET'),
]);

// 1. Initialize
$init = $processor->initialize([
    'amount'  => 1200.00,
    'orderId' => 'ORD-1001',
    'customer' => ['name' => 'Alice', 'mobile' => '01700000000'],
]);

// 2. Redirect / render
if (!empty($init['redirectUrl'])) {
    return redirect($init['redirectUrl']);
}

// 3. Verify in the callback
$verified = $processor->verify(['paymentID' => $init['paymentId']]);

// 4. Refund when needed
$refunded = $processor->refund([
    'paymentID' => $verified['transactionId'],
    'amount'    => 1200.00,
]);
```

### BanglaQR (QR checkout)

BanglaQR lets you render a scannable merchant QR code for Bangladesh payments:

```php
$processor = PaymentProcessor::gateway('banglaqr', [
    'merchant_id' => env('BANGLAQR_MERCHANT_ID'),
    'api_key'     => env('BANGLAQR_API_KEY'),
]);

$init = $processor->initialize([
    'amount'  => 500.00,
    'orderId' => 'ORD-QR-7',
]);

// Render $init['qrImage'] or $init['qrContent'] as a QR code at checkout.
// The customer scans with any participating wallet/banking app.
$verified = $processor->verify(['transactionId' => $init['gatewayReferenceId']]);
```

## Gateway Highlights

bKash · Nagad · Rocket · SSLCommerz · ShurjoPay · AamarPay · SureCash · uPay · **BanglaQR** · Easypaisa · JazzCash · Paytm · PhonePe · Razorpay · Stripe · PayPal · Square · Braintree · Paystack · Flutterwave · M-Pesa · Binance Pay · Alipay · WeChat Pay · UnionPay · Western Union · Wise · GoCardless · Skrill · Neteller · Adyen · Authorize.Net · Worldpay · Klarna · and thousands more via the country catalog.

## Events & Jobs

Hadi Payment ships a full async pipeline — `PaymentInitialized`, `PaymentCompleted`, `PaymentFailed`, `PaymentRefunded` events, queued `ProcessPaymentJob` / `ProcessRefundJob`, and a `PaymentEventListener` that runs AI-assisted anomaly analysis and sends `PaymentNotification` (mail + database).

The sync flow can also persist a `Payment` and dispatch these events automatically — opt in with:

```env
HADI_EVENTS_ENABLED=true
```

## Reports

Transaction reports with real exports — JSON, CSV, SpreadsheetML (Excel), and PDF — plus per-country gateway top lists and a 195-country catalog.

## Testing

```bash
composer test
composer stan    # PHPStan level 5
composer cs      # PHPCS PSR-12
```

180+ PHPUnit tests / 4,600+ assertions, SQLite in-memory, mock HTTP.

## Requirements

- PHP ^8.4
- Laravel ^13.0 | ^14.0
- ext-json, ext-curl, ext-openssl, ext-mbstring, ext-hash, ext-gd

## License

MIT — dedicated to Sharif Osman Bin Hadi.

*Hadi Payment: built for a more connected digital world.*
