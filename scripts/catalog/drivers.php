<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Concrete Driver Mapping
|--------------------------------------------------------------------------
|
| Maps gateway slugs to their concrete driver classes. The generator merges
| this mapping into the master database and country pool, so every catalog
| entry that has a concrete driver resolves to it (rather than the generic
| driver for its type).
|
| The slug must match the gateway key used in master_gateways.php, the
| country pool, and config/hadi-payment.php.
|
*/

return [
    '2checkout' => Hadi\Payment\Gateways\TwoCheckoutGateway::class,
    'adyen' => Hadi\Payment\Gateways\AdyenGateway::class,
    'alipay' => Hadi\Payment\Gateways\AlipayGateway::class,
    'amarpay' => Hadi\Payment\Gateways\AmarPayGateway::class,
    'aamarpay' => Hadi\Payment\Gateways\AmarPayGateway::class,
    'amazonpay' => Hadi\Payment\Gateways\AmazonPayGateway::class,
    'amex' => Hadi\Payment\Gateways\AmericanExpressGateway::class,
    'authorizenet' => Hadi\Payment\Gateways\AuthorizeNetGateway::class,
    'bankdhofar' => Hadi\Payment\Gateways\BankDhofarGateway::class,
    'barq' => Hadi\Payment\Gateways\BarqGateway::class,
    'billdesk' => Hadi\Payment\Gateways\BillDeskGateway::class,
    'binancepay' => Hadi\Payment\Gateways\BinancePayGateway::class,
    'bkash' => Hadi\Payment\Gateways\BkashGateway::class,
    'banglaqr' => Hadi\Payment\Gateways\BanglaQrGateway::class,
    'bluesnap' => Hadi\Payment\Gateways\BlueSnapGateway::class,
    'braintree' => Hadi\Payment\Gateways\BraintreeGateway::class,
    'ccavenue' => Hadi\Payment\Gateways\CCAvenueGateway::class,
    'checkoutcom' => Hadi\Payment\Gateways\CheckoutComGateway::class,
    'dpo' => Hadi\Payment\Gateways\DpoPayGateway::class,
    'easypaisa' => Hadi\Payment\Gateways\EasypaisaGateway::class,
    'flutterwave' => Hadi\Payment\Gateways\FlutterwaveGateway::class,
    'gocardless' => Hadi\Payment\Gateways\GoCardlessGateway::class,
    'googlepay' => Hadi\Payment\Gateways\GooglePayGateway::class,
    'instamojo' => Hadi\Payment\Gateways\InstamojoGateway::class,
    'interswitch' => Hadi\Payment\Gateways\InterswitchGateway::class,
    'jazzcash' => Hadi\Payment\Gateways\JazzcashGateway::class,
    'klarna' => Hadi\Payment\Gateways\KlarnaGateway::class,
    'm-pesa' => Hadi\Payment\Gateways\MpesaGateway::class,
    'mcash' => Hadi\Payment\Gateways\MCashGateway::class,
    'mercury' => Hadi\Payment\Gateways\MercuryGateway::class,
    'mobikwik' => Hadi\Payment\Gateways\MobiKwikGateway::class,
    'mobilypay' => Hadi\Payment\Gateways\MobilyPayGateway::class,
    'mycash' => Hadi\Payment\Gateways\MyCashGateway::class,
    'nagad' => Hadi\Payment\Gateways\NagadGateway::class,
    'neteller' => Hadi\Payment\Gateways\NetellerGateway::class,
    'omantel' => Hadi\Payment\Gateways\OmantelGateway::class,
    'payfast' => Hadi\Payment\Gateways\PayFastGateway::class,
    'payoneer' => Hadi\Payment\Gateways\PayoneerGateway::class,
    'paypal' => Hadi\Payment\Gateways\PayPalGateway::class,
    'paystack' => Hadi\Payment\Gateways\PaystackGateway::class,
    'paytm' => Hadi\Payment\Gateways\PaytmGateway::class,
    'payu' => Hadi\Payment\Gateways\PayUGateway::class,
    'peachpayments' => Hadi\Payment\Gateways\PeachPaymentsGateway::class,
    'phonepe' => Hadi\Payment\Gateways\PhonePeGateway::class,
    'portwallet' => Hadi\Payment\Gateways\PortWalletGateway::class,
    'privacy-com' => Hadi\Payment\Gateways\PrivacyComGateway::class,
    'razorpay' => Hadi\Payment\Gateways\RazorpayGateway::class,
    'revolut-vc' => Hadi\Payment\Gateways\RevolutVirtualCardGateway::class,
    'rocket' => Hadi\Payment\Gateways\RocketGateway::class,
    'sagepay' => Hadi\Payment\Gateways\SagePayGateway::class,
    'shurjopay' => Hadi\Payment\Gateways\ShurjoPayGateway::class,
    'skrill' => Hadi\Payment\Gateways\SkrillGateway::class,
    'square' => Hadi\Payment\Gateways\SquareGateway::class,
    'sslcommerz' => Hadi\Payment\Gateways\SSLCommerzGateway::class,
    'stcpay' => Hadi\Payment\Gateways\StcpayGateway::class,
    'stripe' => Hadi\Payment\Gateways\StripeGateway::class,
    'stripe-issuing' => Hadi\Payment\Gateways\StripeIssuingGateway::class,
    'surecash' => Hadi\Payment\Gateways\SureCashGateway::class,
    'ucash' => Hadi\Payment\Gateways\UCashGateway::class,
    'unionpay' => Hadi\Payment\Gateways\UnionPayGateway::class,
    'upay' => Hadi\Payment\Gateways\UpayGateway::class,
    'urpay' => Hadi\Payment\Gateways\UrpayGateway::class,
    'wechatpay' => Hadi\Payment\Gateways\WeChatPayGateway::class,
    'westernunion' => Hadi\Payment\Gateways\WesternUnionGateway::class,
    'wise' => Hadi\Payment\Gateways\WiseGateway::class,
    'worldpay' => Hadi\Payment\Gateways\WorldpayGateway::class,
];
