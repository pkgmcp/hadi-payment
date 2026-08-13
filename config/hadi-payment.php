<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Hadi Payment Configuration
|--------------------------------------------------------------------------
|
| Hadi Payment is a multi-gateway payment package for PHP 8.4+ and
| Laravel 13/14. This configuration file controls the default gateway,
| per-gateway credentials, security, caching, logging, QR codes, reports
| and notifications.
|
| This package is dedicated to Sharif Osman Bin Hadi.
|
*/

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | The gateway used when no specific gateway is given.
    |
    */

    'default_gateway' => env('HADI_DEFAULT_GATEWAY', 'bkash'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials for every supported gateway. Populate these through your
    | .env file. Each gateway only requires the keys it validates; the rest
    | are optional extras.
    |
    */

    'gateways' => [

        // ---------- Bangladesh ----------

        'bkash' => [
            'appKey' => env('BKASH_APP_KEY'),
            'appSecret' => env('BKASH_APP_SECRET'),
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
            'isSandbox' => env('BKASH_SANDBOX', true),
            'defaultCallbackUrl' => env('BKASH_CALLBACK_URL'),
        ],

        'nagad' => [
            'merchantId' => env('NAGAD_MERCHANT_ID'),
            'merchantPrivateKey' => env('NAGAD_MERCHANT_PRIVATE_KEY'),
            'nagadPublicKey' => env('NAGAD_PUBLIC_KEY'),
            'isSandbox' => env('NAGAD_SANDBOX', true),
            'callbackUrl' => env('NAGAD_CALLBACK_URL'),
        ],

        'rocket' => [
            'api_key' => env('ROCKET_API_KEY'),
            'secret_key' => env('ROCKET_SECRET_KEY'),
            'merchant_id' => env('ROCKET_MERCHANT_ID'),
            'sandbox' => env('ROCKET_SANDBOX', true),
        ],

        'upay' => [
            'apiKey' => env('UPAY_API_KEY'),
            'secretKey' => env('UPAY_SECRET_KEY'),
            'merchantId' => env('UPAY_MERCHANT_ID'),
            'isSandbox' => env('UPAY_SANDBOX', true),
            'callbackUrl' => env('UPAY_CALLBACK_URL'),
        ],

        'surecash' => [
            'api_key' => env('SURECASH_API_KEY'),
            'secret_key' => env('SURECASH_SECRET_KEY'),
            'merchant_id' => env('SURECASH_MERCHANT_ID'),
            'sandbox' => env('SURECASH_SANDBOX', true),
        ],

        'banglaqr' => [
            'merchant_id' => env('BANGLAQR_MERCHANT_ID'),
            'api_key' => env('BANGLAQR_API_KEY'),
            'sandbox' => env('BANGLAQR_SANDBOX', true),
        ],

        'ucash' => [
            'api_key' => env('UCASH_API_KEY'),
            'secret_key' => env('UCASH_SECRET_KEY'),
            'merchant_id' => env('UCASH_MERCHANT_ID'),
            'sandbox' => env('UCASH_SANDBOX', true),
        ],

        'mcash' => [
            'api_key' => env('MCASH_API_KEY'),
            'secret_key' => env('MCASH_SECRET_KEY'),
            'merchant_id' => env('MCASH_MERCHANT_ID'),
            'sandbox' => env('MCASH_SANDBOX', true),
        ],

        'mycash' => [
            'api_key' => env('MYCASH_API_KEY'),
            'secret_key' => env('MYCASH_SECRET_KEY'),
            'merchant_id' => env('MYCASH_MERCHANT_ID'),
            'sandbox' => env('MYCASH_SANDBOX', true),
        ],

        'shurjopay' => [
            'merchant_id' => env('SHURJOPAY_MERCHANT_ID'),
            'merchant_password' => env('SHURJOPAY_MERCHANT_PASSWORD'),
            'api_key' => env('SHURJOPAY_API_KEY'),
            'sandbox' => env('SHURJOPAY_SANDBOX', true),
        ],

        'sslcommerz' => [
            'store_id' => env('SSLCOMMERZ_STORE_ID'),
            'store_passwd' => env('SSLCOMMERZ_STORE_PASSWORD'),
            'isSandbox' => env('SSLCOMMERZ_SANDBOX', true),
            'currency' => env('SSLCOMMERZ_CURRENCY', 'BDT'),
        ],

        'amarpay' => [
            'store_id' => env('AAMARPAY_STORE_ID'),
            'signature_key' => env('AAMARPAY_SIGNATURE_KEY'),
            'isSandbox' => env('AAMARPAY_SANDBOX', true),
            'currency' => env('AAMARPAY_CURRENCY', 'BDT'),
        ],

        // ---------- Global wallets & crypto ----------

        'binance' => [
            'apiKey' => env('BINANCE_API_KEY'),
            'secretKey' => env('BINANCE_SECRET_KEY'),
            'isSandbox' => env('BINANCE_SANDBOX', true),
        ],

        'paypal' => [
            'clientId' => env('PAYPAL_CLIENT_ID'),
            'clientSecret' => env('PAYPAL_CLIENT_SECRET'),
            'isSandbox' => env('PAYPAL_SANDBOX', true),
            'brandName' => env('PAYPAL_BRAND_NAME', 'Hadi Payment'),
        ],

        'stripe' => [
            'publishableKey' => env('STRIPE_PUBLISHABLE_KEY'),
            'secretKey' => env('STRIPE_SECRET_KEY'),
            'webhookSecret' => env('STRIPE_WEBHOOK_SECRET'),
            'isSandbox' => env('STRIPE_SANDBOX', true),
        ],

        'razorpay' => [
            'keyId' => env('RAZORPAY_KEY_ID'),
            'keySecret' => env('RAZORPAY_KEY_SECRET'),
            'webhookSecret' => env('RAZORPAY_WEBHOOK_SECRET'),
            'receiveCurrency' => env('RAZORPAY_CURRENCY', 'INR'),
        ],

        'paytm' => [
            'merchantId' => env('PAYTM_MERCHANT_ID'),
            'merchantKey' => env('PAYTM_MERCHANT_KEY'),
            'websiteName' => env('PAYTM_WEBSITE_NAME', 'WEBSTAGING'),
            'industryTypeId' => env('PAYTM_INDUSTRY_TYPE_ID', 'Retail'),
            'channelId' => env('PAYTM_CHANNEL_ID', 'WEB'),
            'isSandbox' => env('PAYTM_SANDBOX', true),
        ],

        'phonepe' => [
            'merchantId' => env('PHONEPE_MERCHANT_ID'),
            'saltKey' => env('PHONEPE_SALT_KEY'),
            'saltIndex' => env('PHONEPE_SALT_INDEX'),
            'isSandbox' => env('PHONEPE_SANDBOX', true),
        ],

        'stcpay' => [
            'merchantId' => env('STCPAY_MERCHANT_ID'),
            'apiKey' => env('STCPAY_API_KEY'),
            'secretKey' => env('STCPAY_SECRET_KEY'),
            'isSandbox' => env('STCPAY_SANDBOX', true),
        ],

        'easypaisa' => [
            'storeId' => env('EASYPAISA_STORE_ID'),
            'hashKey' => env('EASYPAISA_HASH_KEY'),
            'paymentMethod' => env('EASYPAISA_PAYMENT_METHOD', 'MA'),
            'isSandbox' => env('EASYPAISA_SANDBOX', true),
        ],

        'jazzcash' => [
            'merchantId' => env('JAZZCASH_MERCHANT_ID'),
            'password' => env('JAZZCASH_PASSWORD'),
            'integritySalt' => env('JAZZCASH_INTEGRITY_SALT'),
            'isSandbox' => env('JAZZCASH_SANDBOX', true),
            'currency' => env('JAZZCASH_CURRENCY', 'PKR'),
        ],

        'mpesa' => [
            'consumerKey' => env('MPESA_CONSUMER_KEY'),
            'consumerSecret' => env('MPESA_CONSUMER_SECRET'),
            'shortCode' => env('MPESA_SHORT_CODE'),
            'passkey' => env('MPESA_PASSKEY'),
            'transactionType' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),
            'isSandbox' => env('MPESA_SANDBOX', true),
        ],

        'paystack' => [
            'secretKey' => env('PAYSTACK_SECRET_KEY'),
            'publicKey' => env('PAYSTACK_PUBLIC_KEY'),
        ],

        'flutterwave' => [
            'secretKey' => env('FLUTTERWAVE_SECRET_KEY'),
            'publicKey' => env('FLUTTERWAVE_PUBLIC_KEY'),
            'encryptionKey' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
            'isSandbox' => env('FLUTTERWAVE_SANDBOX', true),
        ],

        'payfast' => [
            'merchantId' => env('PAYFAST_MERCHANT_ID'),
            'merchantKey' => env('PAYFAST_MERCHANT_KEY'),
            'passphrase' => env('PAYFAST_PASSPHRASE'),
            'isSandbox' => env('PAYFAST_SANDBOX', true),
        ],

        // ---------- International ----------

        'adyen' => [
            'merchantAccount' => env('ADYEN_MERCHANT_ACCOUNT'),
            'apiKey' => env('ADYEN_API_KEY'),
            'clientKey' => env('ADYEN_CLIENT_KEY'),
            'liveEndpointPrefix' => env('ADYEN_LIVE_ENDPOINT_PREFIX'),
            'hmacKey' => env('ADYEN_HMAC_KEY'),
            'isSandbox' => env('ADYEN_SANDBOX', true),
        ],

        'alipay' => [
            'app_id' => env('ALIPAY_APP_ID'),
            'merchant_private_key' => env('ALIPAY_MERCHANT_PRIVATE_KEY'),
            'alipay_public_key' => env('ALIPAY_PUBLIC_KEY'),
            'isSandbox' => env('ALIPAY_SANDBOX', true),
        ],

        'wechatpay' => [
            'app_id' => env('WECHATPAY_APP_ID'),
            'mch_id' => env('WECHATPAY_MCH_ID'),
            'merchant_serial_no' => env('WECHATPAY_MERCHANT_SERIAL_NO'),
            'merchant_private_key_path' => env('WECHATPAY_MERCHANT_PRIVATE_KEY_PATH'),
            'wechatpay_platform_certificate_path' => env('WECHATPAY_PLATFORM_CERT_PATH'),
            'isSandbox' => env('WECHATPAY_SANDBOX', true),
        ],

        'amazonpay' => [
            'merchantId' => env('AMAZONPAY_MERCHANT_ID'),
            'accessKey' => env('AMAZONPAY_ACCESS_KEY'),
            'secretKey' => env('AMAZONPAY_SECRET_KEY'),
            'clientId' => env('AMAZONPAY_CLIENT_ID'),
            'region' => env('AMAZONPAY_REGION', 'us'),
            'publicKeyId' => env('AMAZONPAY_PUBLIC_KEY_ID'),
            'privateKeyPath' => env('AMAZONPAY_PRIVATE_KEY_PATH'),
            'isSandbox' => env('AMAZONPAY_SANDBOX', true),
        ],

        'amex' => [
            'merchantId' => env('AMEX_MERCHANT_ID'),
            'apiKey' => env('AMEX_API_KEY'),
            'apiPassword' => env('AMEX_API_PASSWORD'),
            'isSandbox' => env('AMEX_SANDBOX', true),
        ],

        'authorizenet' => [
            'apiLoginId' => env('AUTHORIZENET_API_LOGIN_ID'),
            'transactionKey' => env('AUTHORIZENET_TRANSACTION_KEY'),
            'signatureKey' => env('AUTHORIZENET_SIGNATURE_KEY'),
            'solutionId' => env('AUTHORIZENET_SOLUTION_ID'),
            'isSandbox' => env('AUTHORIZENET_SANDBOX', true),
        ],

        'bankdhofar' => [
            'merchantId' => env('BANKDHOFAR_MERCHANT_ID'),
            'apiKey' => env('BANKDHOFAR_API_KEY'),
            'secretKey' => env('BANKDHOFAR_SECRET_KEY'),
            'terminalId' => env('BANKDHOFAR_TERMINAL_ID'),
            'isSandbox' => env('BANKDHOFAR_SANDBOX', true),
        ],

        'barq' => [
            'apiKey' => env('BARQ_API_KEY'),
            'secretKey' => env('BARQ_SECRET_KEY'),
            'merchantId' => env('BARQ_MERCHANT_ID'),
            'isSandbox' => env('BARQ_SANDBOX', true),
        ],

        'billdesk' => [
            'merchantId' => env('BILLDESK_MERCHANT_ID'),
            'checksumKey' => env('BILLDESK_CHECKSUM_KEY'),
            'securityId' => env('BILLDESK_SECURITY_ID'),
            'isSandbox' => env('BILLDESK_SANDBOX', true),
        ],

        'bluesnap' => [
            'apiUsername' => env('BLUESNAP_API_USERNAME'),
            'apiPassword' => env('BLUESNAP_API_PASSWORD'),
            'storeId' => env('BLUESNAP_STORE_ID'),
            'softDescriptor' => env('BLUESNAP_SOFT_DESCRIPTOR'),
            'isSandbox' => env('BLUESNAP_SANDBOX', true),
        ],

        'braintree' => [
            'environment' => env('BRAINTREE_ENVIRONMENT', 'sandbox'),
            'merchantId' => env('BRAINTREE_MERCHANT_ID'),
            'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
            'privateKey' => env('BRAINTREE_PRIVATE_KEY'),
        ],

        'ccavenue' => [
            'merchantId' => env('CCAVENUE_MERCHANT_ID'),
            'accessCode' => env('CCAVENUE_ACCESS_CODE'),
            'workingKey' => env('CCAVENUE_WORKING_KEY'),
            'isSandbox' => env('CCAVENUE_SANDBOX', true),
        ],

        'checkoutcom' => [
            'secretKey' => env('CHECKOUTCOM_SECRET_KEY'),
            'publicKey' => env('CHECKOUTCOM_PUBLIC_KEY'),
            'webhookSecret' => env('CHECKOUTCOM_WEBHOOK_SECRET'),
            'processingChannelId' => env('CHECKOUTCOM_PROCESSING_CHANNEL_ID'),
            'isSandbox' => env('CHECKOUTCOM_SANDBOX', true),
        ],

        'dpopay' => [
            'companyToken' => env('DPOPAY_COMPANY_TOKEN'),
            'serviceType' => env('DPOPAY_SERVICE_TYPE', '3859'),
            'isSandbox' => env('DPOPAY_SANDBOX', true),
        ],

        'gocardless' => [
            'accessToken' => env('GOCARDLESS_ACCESS_TOKEN'),
            'webhookSecret' => env('GOCARDLESS_WEBHOOK_SECRET'),
            'mode' => env('GOCARDLESS_MODE', 'sandbox'),
        ],

        'googlepay' => [
            'processorMerchantId' => env('GOOGLEPAY_PROCESSOR_MERCHANT_ID'),
            'processorApiKey' => env('GOOGLEPAY_PROCESSOR_API_KEY'),
            'processorApiSecret' => env('GOOGLEPAY_PROCESSOR_API_SECRET'),
            'googlePayMerchantId' => env('GOOGLEPAY_MERCHANT_ID'),
            'gatewayName' => env('GOOGLEPAY_GATEWAY_NAME', ''),
            'isSandbox' => env('GOOGLEPAY_SANDBOX', true),
        ],

        'instamojo' => [
            'apiKey' => env('INSTAMOJO_API_KEY'),
            'authToken' => env('INSTAMOJO_AUTH_TOKEN'),
            'isSandbox' => env('INSTAMOJO_SANDBOX', true),
        ],

        'interswitch' => [
            'productId' => env('INTERSWITCH_PRODUCT_ID'),
            'merchantId' => env('INTERSWITCH_MERCHANT_ID'),
            'apiKey' => env('INTERSWITCH_API_KEY'),
            'macKey' => env('INTERSWITCH_MAC_KEY'),
            'terminalId' => env('INTERSWITCH_TERMINAL_ID'),
            'clientId' => env('INTERSWITCH_CLIENT_ID'),
            'clientSecret' => env('INTERSWITCH_CLIENT_SECRET'),
            'isSandbox' => env('INTERSWITCH_SANDBOX', true),
        ],

        'klarna' => [
            'username' => env('KLARNA_USERNAME'),
            'password' => env('KLARNA_PASSWORD'),
            'region' => env('KLARNA_REGION', 'eu'),
            'isSandbox' => env('KLARNA_SANDBOX', true),
            'purchaseCountry' => env('KLARNA_PURCHASE_COUNTRY', 'GB'),
            'purchaseCurrency' => env('KLARNA_PURCHASE_CURRENCY', 'GBP'),
            'locale' => env('KLARNA_LOCALE', 'en-GB'),
        ],

        'mobikwik' => [
            'merchantId' => env('MOBIKWIK_MERCHANT_ID'),
            'secretKey' => env('MOBIKWIK_SECRET_KEY'),
            'isSandbox' => env('MOBIKWIK_SANDBOX', true),
        ],

        'mobilypay' => [
            'merchantId' => env('MOBILYPAY_MERCHANT_ID'),
            'apiKey' => env('MOBILYPAY_API_KEY'),
            'secretKey' => env('MOBILYPAY_SECRET_KEY'),
            'isSandbox' => env('MOBILYPAY_SANDBOX', true),
        ],

        'neteller' => [
            'accountId' => env('NETELLER_ACCOUNT_ID'),
            'apiKey' => env('NETELLER_API_KEY'),
            'isSandbox' => env('NETELLER_SANDBOX', true),
        ],

        'omantel' => [
            'merchantId' => env('OMANTEL_MERCHANT_ID'),
            'apiKey' => env('OMANTEL_API_KEY'),
            'apiSecret' => env('OMANTEL_API_SECRET'),
            'isSandbox' => env('OMANTEL_SANDBOX', true),
        ],

        'payu' => [
            'merchantKey' => env('PAYU_MERCHANT_KEY'),
            'salt' => env('PAYU_SALT'),
            'authHeader' => env('PAYU_AUTH_HEADER'),
            'accountId' => env('PAYU_ACCOUNT_ID'),
            'merchantId' => env('PAYU_MERCHANT_ID'),
            'apiLogin' => env('PAYU_API_LOGIN'),
            'apiKey' => env('PAYU_API_KEY'),
            'isSandbox' => env('PAYU_SANDBOX', true),
        ],

        'payoneer' => [
            'programId' => env('PAYONEER_PROGRAM_ID'),
            'apiUsername' => env('PAYONEER_API_USERNAME'),
            'apiPassword' => env('PAYONEER_API_PASSWORD'),
            'payoutCurrency' => env('PAYONEER_PAYOUT_CURRENCY', 'USD'),
            'isSandbox' => env('PAYONEER_SANDBOX', true),
        ],

        'peachpayments' => [
            'entityId' => env('PEACHPAYMENTS_ENTITY_ID'),
            'secretToken' => env('PEACHPAYMENTS_SECRET_TOKEN'),
            'checkoutEntityId' => env('PEACHPAYMENTS_CHECKOUT_ENTITY_ID'),
            'currency' => env('PEACHPAYMENTS_CURRENCY', 'ZAR'),
            'isSandbox' => env('PEACHPAYMENTS_SANDBOX', true),
        ],

        'portwallet' => [
            'app_key' => env('PORTWALLET_APP_KEY'),
            'secret_key' => env('PORTWALLET_SECRET_KEY'),
            'currency' => env('PORTWALLET_CURRENCY', 'BDT'),
            'isSandbox' => env('PORTWALLET_SANDBOX', true),
        ],

        'sagepay' => [
            'vendorName' => env('SAGEPAY_VENDOR_NAME'),
            'encryptionPassword' => env('SAGEPAY_ENCRYPTION_PASSWORD'),
            'integrationType' => env('SAGEPAY_INTEGRATION_TYPE', 'form'),
            'referrerId' => env('SAGEPAY_REFERRER_ID'),
            'isSandbox' => env('SAGEPAY_SANDBOX', true),
        ],

        'skrill' => [
            'merchantEmail' => env('SKRILL_MERCHANT_EMAIL'),
            'secretWord' => env('SKRILL_SECRET_WORD'),
            'mqiPassword' => env('SKRILL_MQI_PASSWORD'),
            'merchantId' => env('SKRILL_MERCHANT_ID'),
            'isSandbox' => env('SKRILL_SANDBOX', true),
        ],

        'square' => [
            'accessToken' => env('SQUARE_ACCESS_TOKEN'),
            'locationId' => env('SQUARE_LOCATION_ID'),
            'isSandbox' => env('SQUARE_SANDBOX', true),
        ],

        'twocheckout' => [
            'sellerId' => env('TWOCCHECKOUT_SELLER_ID'),
            'privateKey' => env('TWOCCHECKOUT_PRIVATE_KEY'),
            'secretWord' => env('TWOCCHECKOUT_SECRET_WORD'),
            'publishableKey' => env('TWOCCHECKOUT_PUBLISHABLE_KEY'),
            'isSandbox' => env('TWOCCHECKOUT_SANDBOX', true),
        ],

        'unionpay' => [
            'merId' => env('UNIONPAY_MER_ID'),
            'signCertPath' => env('UNIONPAY_SIGN_CERT_PATH'),
            'signCertPassword' => env('UNIONPAY_SIGN_CERT_PASSWORD'),
            'encryptCertPath' => env('UNIONPAY_ENCRYPT_CERT_PATH'),
            'verifyCertPathDir' => env('UNIONPAY_VERIFY_CERT_PATH_DIR'),
            'isSandbox' => env('UNIONPAY_SANDBOX', true),
        ],

        'urpay' => [
            'clientId' => env('URPAY_CLIENT_ID'),
            'clientSecret' => env('URPAY_CLIENT_SECRET'),
            'terminalId' => env('URPAY_TERMINAL_ID'),
            'isSandbox' => env('URPAY_SANDBOX', true),
        ],

        'westernunion' => [
            'apiKey' => env('WESTERNUNION_API_KEY'),
            'apiSecret' => env('WESTERNUNION_API_SECRET'),
            'programId' => env('WESTERNUNION_PROGRAM_ID'),
            'isSandbox' => env('WESTERNUNION_SANDBOX', true),
        ],

        'wise' => [
            'apiToken' => env('WISE_API_TOKEN'),
            'profileId' => env('WISE_PROFILE_ID'),
            'webhookSecret' => env('WISE_WEBHOOK_SECRET'),
            'isSandbox' => env('WISE_SANDBOX', true),
        ],

        'worldpay' => [
            'serviceKey' => env('WORLDPAY_SERVICE_KEY'),
            'clientKey' => env('WORLDPAY_CLIENT_KEY'),
            'webhookSecret' => env('WORLDPAY_WEBHOOK_SECRET'),
            'isSandbox' => env('WORLDPAY_SANDBOX', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Payment Settings
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'currency' => env('HADI_DEFAULT_CURRENCY', 'BDT'),
        'timeout' => env('HADI_TIMEOUT', 30),
        'retry_attempts' => env('HADI_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('HADI_RETRY_DELAY', 1000),
        'system_user_id' => env('HADI_SYSTEM_USER_ID', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Configuration
    |--------------------------------------------------------------------------
    */

    'invoice' => [
        'prefix' => env('HADI_INVOICE_PREFIX', 'INV'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Configuration
    |--------------------------------------------------------------------------
    |
    | When enabled, the sync payment flow persists a Payment model and
    | dispatches the corresponding events (PaymentInitialized, PaymentCompleted,
    | PaymentFailed, PaymentRefunded), which the PaymentEventListener turns into
    | logging, AI analysis and notifications. When disabled (default), the sync
    | flow is stateless and no Payment records or events are produced.
    |
    */

    'events' => [
        'enabled' => env('HADI_EVENTS_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => env('HADI_LOGGING_ENABLED', true),
        'level' => env('HADI_LOG_LEVEL', 'info'),
        'channel' => env('HADI_LOG_CHANNEL', 'payment'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'max_attempts' => env('HADI_RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('HADI_RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */

    'webhooks' => [
        'enabled' => env('HADI_WEBHOOK_ENABLED', true),
        'secret' => env('HADI_WEBHOOK_SECRET'),
        'url' => env('HADI_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */

    'security' => [
        'encrypt_sensitive_data' => env('HADI_ENCRYPT_SENSITIVE_DATA', true),
        'sanitize_logs' => env('HADI_SANITIZE_LOGS', true),
        'require_https' => env('HADI_REQUIRE_HTTPS', true),
        'webhook_secret' => env('HADI_WEBHOOK_SECRET'),
        'hash_secret' => env('HADI_HASH_SECRET', env('APP_KEY')),
        'transaction_prefix' => env('HADI_TRANSACTION_PREFIX', 'TXN'),
        'reference_prefix' => env('HADI_REFERENCE_PREFIX', 'REF'),
        'max_amount' => env('HADI_MAX_AMOUNT', 1000000),
        'min_amount' => env('HADI_MIN_AMOUNT', 0.01),
        'rate_limit' => [
            'enabled' => env('HADI_RATE_LIMIT_ENABLED', true),
            'max_attempts' => env('HADI_RATE_LIMIT_MAX_ATTEMPTS', 5),
            'window_minutes' => env('HADI_RATE_LIMIT_WINDOW_MINUTES', 15),
        ],
        'fraud_detection' => [
            'enabled' => env('HADI_FRAUD_DETECTION_ENABLED', true),
            'suspicious_ip_check' => env('HADI_SUSPICIOUS_IP_CHECK', true),
            'rapid_payment_check' => env('HADI_RAPID_PAYMENT_CHECK', true),
            'unusual_amount_check' => env('HADI_UNUSUAL_AMOUNT_CHECK', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'enabled' => env('HADI_CACHE_ENABLED', true),
        'driver' => env('HADI_CACHE_DRIVER', 'redis'),
        'prefix' => env('HADI_CACHE_PREFIX', 'hadi_payment'),
        'ttl' => env('HADI_CACHE_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | QR Code Configuration
    |--------------------------------------------------------------------------
    */

    'qr_code' => [
        'enabled' => env('HADI_QR_CODE_ENABLED', true),
        'storage_path' => env('HADI_QR_CODE_STORAGE_PATH', 'qr-codes'),
        'size' => env('HADI_QR_CODE_SIZE', 200),
        'format' => env('HADI_QR_CODE_FORMAT', 'png'),
        'error_correction' => env('HADI_QR_CODE_ERROR_CORRECTION', 'M'),
        'margin' => env('HADI_QR_CODE_MARGIN', 1),
        'cleanup_days' => env('HADI_QR_CODE_CLEANUP_DAYS', 30),
        // Optional URL templates embedded in QR payloads. A template is only
        // used when a matching route is registered; otherwise the field is
        // omitted from the payload. Placeholders: {id}
        'urls' => [
            'payment' => env('HADI_QR_PAYMENT_URL', 'payment.form'),
            'invoice' => env('HADI_QR_INVOICE_URL', 'admin.payment.invoices.show'),
            'refund' => env('HADI_QR_REFUND_URL', 'payment.initialize'),
            'receipt' => env('HADI_QR_RECEIPT_URL', 'payment.success'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reports Configuration
    |--------------------------------------------------------------------------
    */

    'reports' => [
        'enabled' => env('HADI_REPORTS_ENABLED', true),
        'cache_ttl' => env('HADI_REPORTS_CACHE_TTL', 3600),
        'export_formats' => ['json', 'csv', 'excel', 'pdf'],
        'dashboard_refresh' => env('HADI_REPORTS_DASHBOARD_REFRESH', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Agent Configuration
    |--------------------------------------------------------------------------
    */

    'ai_agent' => [
        'enabled' => env('HADI_AI_AGENT_ENABLED', true),
        'auto_resolve' => env('HADI_AI_AGENT_AUTO_RESOLVE', false),
        'risk_threshold' => env('HADI_AI_AGENT_RISK_THRESHOLD', 70),
        'max_insights' => env('HADI_AI_AGENT_MAX_INSIGHTS', 10),
        'notifications_enabled' => env('HADI_AI_AGENT_NOTIFICATIONS_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications Configuration
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'mail' => [
            'enabled' => env('HADI_NOTIFICATIONS_MAIL_ENABLED', true),
            'from_address' => env('HADI_NOTIFICATIONS_FROM_ADDRESS', 'noreply@example.com'),
            'from_name' => env('HADI_NOTIFICATIONS_FROM_NAME', 'Hadi Payment'),
            'recipient' => env('HADI_NOTIFICATIONS_RECIPIENT_MAIL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Country Catalog
    |--------------------------------------------------------------------------
    |
    | Hadi Payment ships a catalog of the top gateways for all 195 countries
    | (see src/Data/gateways_by_country.php). Every catalog gateway is
    | registered automatically by the GatewayFactory. Gateways without an
    | explicit block above are configured through the generic environment
    | convention HADI_<GATEWAY>_* (e.g. HADI_MTN_MOMO_MERCHANT_ID,
    | HADI_MTN_MOMO_API_KEY, HADI_MTN_MOMO_ENDPOINT, HADI_MTN_MOMO_SANDBOX).
    | Concrete drivers keep using their dedicated configuration blocks.
    |
    */
];
