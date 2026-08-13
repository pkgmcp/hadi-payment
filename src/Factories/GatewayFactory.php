<?php

declare(strict_types=1);

namespace Hadi\Payment\Factories;

use Hadi\Payment\Contracts\GatewayFactoryInterface;
use Hadi\Payment\Contracts\PaymentGatewayInterface;
use Hadi\Payment\Exceptions\ConfigurationException;
use Hadi\Payment\Gateways\AdyenGateway;
use Hadi\Payment\Gateways\AlipayGateway;
use Hadi\Payment\Gateways\AmarPayGateway;
use Hadi\Payment\Gateways\AmazonPayGateway;
use Hadi\Payment\Gateways\AmericanExpressGateway;
use Hadi\Payment\Gateways\AuthorizeNetGateway;
use Hadi\Payment\Gateways\BanglaQrGateway;
use Hadi\Payment\Gateways\BankDhofarGateway;
use Hadi\Payment\Gateways\BarqGateway;
use Hadi\Payment\Gateways\BillDeskGateway;
use Hadi\Payment\Gateways\BinancePayGateway;
use Hadi\Payment\Gateways\BkashGateway;
use Hadi\Payment\Gateways\BlueSnapGateway;
use Hadi\Payment\Gateways\BraintreeGateway;
use Hadi\Payment\Gateways\CCAvenueGateway;
use Hadi\Payment\Gateways\CheckoutComGateway;
use Hadi\Payment\Gateways\DpoPayGateway;
use Hadi\Payment\Gateways\EasypaisaGateway;
use Hadi\Payment\Gateways\FlutterwaveGateway;
use Hadi\Payment\Gateways\GoCardlessGateway;
use Hadi\Payment\Gateways\GooglePayGateway;
use Hadi\Payment\Gateways\InstamojoGateway;
use Hadi\Payment\Gateways\InterswitchGateway;
use Hadi\Payment\Gateways\JazzcashGateway;
use Hadi\Payment\Gateways\KlarnaGateway;
use Hadi\Payment\Gateways\MCashGateway;
use Hadi\Payment\Gateways\MobiKwikGateway;
use Hadi\Payment\Gateways\MobilyPayGateway;
use Hadi\Payment\Gateways\MpesaGateway;
use Hadi\Payment\Gateways\MyCashGateway;
use Hadi\Payment\Gateways\NagadGateway;
use Hadi\Payment\Gateways\NetellerGateway;
use Hadi\Payment\Gateways\OmantelGateway;
use Hadi\Payment\Gateways\PayFastGateway;
use Hadi\Payment\Gateways\PayUGateway;
use Hadi\Payment\Gateways\PayPalGateway;
use Hadi\Payment\Gateways\PayoneerGateway;
use Hadi\Payment\Gateways\PaystackGateway;
use Hadi\Payment\Gateways\PaytmGateway;
use Hadi\Payment\Gateways\PeachPaymentsGateway;
use Hadi\Payment\Gateways\PhonePeGateway;
use Hadi\Payment\Gateways\PortWalletGateway;
use Hadi\Payment\Gateways\RazorpayGateway;
use Hadi\Payment\Gateways\RocketGateway;
use Hadi\Payment\Gateways\SSLCommerzGateway;
use Hadi\Payment\Gateways\SagePayGateway;
use Hadi\Payment\Gateways\ShurjoPayGateway;
use Hadi\Payment\Gateways\SkrillGateway;
use Hadi\Payment\Gateways\SquareGateway;
use Hadi\Payment\Gateways\StcpayGateway;
use Hadi\Payment\Gateways\StripeGateway;
use Hadi\Payment\Gateways\SureCashGateway;
use Hadi\Payment\Gateways\TwoCheckoutGateway;
use Hadi\Payment\Gateways\UCashGateway;
use Hadi\Payment\Gateways\UnionPayGateway;
use Hadi\Payment\Gateways\UpayGateway;
use Hadi\Payment\Gateways\UrpayGateway;
use Hadi\Payment\Gateways\WeChatPayGateway;
use Hadi\Payment\Gateways\WesternUnionGateway;
use Hadi\Payment\Gateways\WiseGateway;
use Hadi\Payment\Gateways\WorldpayGateway;
use Hadi\Payment\PaymentGateway;
use Hadi\Payment\Services\CountryCatalog;
use Illuminate\Support\Facades\Log;

class GatewayFactory implements GatewayFactoryInterface
{
    private array $gateways = [];
    private array $customGateways = [];
    private array $gatewayNames = [];
    private CountryCatalog $catalog;

    public function __construct()
    {
        $this->registerDefaultGateways();
        $this->catalog = new CountryCatalog();
        $this->registerCountryGateways();
    }

    /**
     * Create a payment gateway instance.
     */
    public function create(string $gateway, array $config): PaymentGatewayInterface
    {
        $gateway = strtolower($gateway);

        if (!$this->isGatewayAvailable($gateway)) {
            throw new ConfigurationException("Gateway '{$gateway}' is not available");
        }

        $gatewayClass = $this->getGatewayClass($gateway);

        if (!class_exists($gatewayClass)) {
            throw new ConfigurationException("Gateway class '{$gatewayClass}' does not exist");
        }

        try {
            return new $gatewayClass($config);
        } catch (\Exception $e) {
            Log::error("Failed to create gateway '{$gateway}'", [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            throw new ConfigurationException("Failed to create gateway '{$gateway}': " . $e->getMessage());
        }
    }

    /**
     * Get all available gateways.
     */
    public function getAvailableGateways(): array
    {
        return array_merge($this->gateways, $this->customGateways);
    }

    /**
     * Check if gateway is available.
     */
    public function isGatewayAvailable(string $gateway): bool
    {
        $gateway = strtolower($gateway);

        return isset($this->gateways[$gateway]) || isset($this->customGateways[$gateway]);
    }

    /**
     * Register a custom gateway.
     */
    public function registerGateway(string $name, string $class): void
    {
        $name = strtolower($name);

        if (!class_exists($class)) {
            throw new ConfigurationException("Gateway class '{$class}' does not exist");
        }

        if (!is_subclass_of($class, PaymentGateway::class)) {
            throw new ConfigurationException("Gateway class '{$class}' must extend " . PaymentGateway::class);
        }

        $this->customGateways[$name] = $class;

        Log::info("Custom gateway registered", [
            'name' => $name,
            'class' => $class,
        ]);
    }

    /**
     * Get gateway configuration.
     */
    public function getGatewayConfig(string $gateway): array
    {
        $gateway = strtolower($gateway);
        $config = config("hadi-payment.gateways.{$gateway}", []);

        if (empty($config)) {
            $config = $this->catalogConfig($gateway);
        }

        if (empty($config)) {
            throw new ConfigurationException("Configuration for gateway '{$gateway}' not found");
        }

        return $config;
    }

    /**
     * Get the human friendly display name for a gateway.
     */
    public function getGatewayName(string $gateway): string
    {
        $gateway = strtolower($gateway);

        if (isset($this->gatewayNames[$gateway])) {
            return $this->gatewayNames[$gateway];
        }

        if (isset($this->gateways[$gateway])) {
            $name = basename(str_replace('\\', '/', $this->gateways[$gateway]));

            return preg_replace('/(?<!^)[A-Z]/', ' $0', preg_replace('/Gateway$/', '', $name)) ?? $gateway;
        }

        return $gateway;
    }

    /**
     * Get the CountryCatalog used by this factory.
     */
    public function getCatalog(): CountryCatalog
    {
        return $this->catalog;
    }

    /**
     * Build a generic env-driven configuration block for catalog gateways
     * that have no explicit config section (HADI_<GATEWAY>_* variables).
     */
    private function catalogConfig(string $gateway): array
    {
        if (!$this->catalog->hasGateway($gateway)) {
            return [];
        }

        $key = strtoupper(str_replace('-', '_', $gateway));

        return array_filter([
            'merchant_id' => env("HADI_{$key}_MERCHANT_ID"),
            'api_key' => env("HADI_{$key}_API_KEY"),
            'secret_key' => env("HADI_{$key}_SECRET_KEY"),
            'endpoint' => env("HADI_{$key}_ENDPOINT"),
            'sandbox' => env("HADI_{$key}_SANDBOX", true),
            'defaultCallbackUrl' => env("HADI_{$key}_CALLBACK_URL"),
            'currency' => env("HADI_{$key}_CURRENCY"),
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Register all built-in gateways.
     */
    private function registerDefaultGateways(): void
    {
        $this->gateways = [
            'adyen' => AdyenGateway::class,
            'alipay' => AlipayGateway::class,
            'amarpay' => AmarPayGateway::class,
            'aamarpay' => AmarPayGateway::class,
            'amazonpay' => AmazonPayGateway::class,
            'amex' => AmericanExpressGateway::class,
            'americanexpress' => AmericanExpressGateway::class,
            'authorizenet' => AuthorizeNetGateway::class,
            'bankdhofar' => BankDhofarGateway::class,
            'barq' => BarqGateway::class,
            'billdesk' => BillDeskGateway::class,
            'binance' => BinancePayGateway::class,
            'binancepay' => BinancePayGateway::class,
            'bkash' => BkashGateway::class,
            'banglaqr' => BanglaQrGateway::class,
            'bluesnap' => BlueSnapGateway::class,
            'braintree' => BraintreeGateway::class,
            'ccavenue' => CCAvenueGateway::class,
            'checkoutcom' => CheckoutComGateway::class,
            'dpopay' => DpoPayGateway::class,
            'easypaisa' => EasypaisaGateway::class,
            'flutterwave' => FlutterwaveGateway::class,
            'gocardless' => GoCardlessGateway::class,
            'googlepay' => GooglePayGateway::class,
            'instamojo' => InstamojoGateway::class,
            'interswitch' => InterswitchGateway::class,
            'jazzcash' => JazzcashGateway::class,
            'klarna' => KlarnaGateway::class,
            'mcash' => MCashGateway::class,
            'mobikwik' => MobiKwikGateway::class,
            'mobilypay' => MobilyPayGateway::class,
            'mpesa' => MpesaGateway::class,
            'mycash' => MyCashGateway::class,
            'nagad' => NagadGateway::class,
            'neteller' => NetellerGateway::class,
            'omantel' => OmantelGateway::class,
            'payfast' => PayFastGateway::class,
            'paypal' => PayPalGateway::class,
            'paytm' => PaytmGateway::class,
            'payu' => PayUGateway::class,
            'payoneer' => PayoneerGateway::class,
            'paystack' => PaystackGateway::class,
            'peachpayments' => PeachPaymentsGateway::class,
            'phonepe' => PhonePeGateway::class,
            'portwallet' => PortWalletGateway::class,
            'razorpay' => RazorpayGateway::class,
            'rocket' => RocketGateway::class,
            'sagepay' => SagePayGateway::class,
            'shurjopay' => ShurjoPayGateway::class,
            'skrill' => SkrillGateway::class,
            'square' => SquareGateway::class,
            'sslcommerz' => SSLCommerzGateway::class,
            'stcpay' => StcpayGateway::class,
            'stripe' => StripeGateway::class,
            'surecash' => SureCashGateway::class,
            'twocheckout' => TwoCheckoutGateway::class,
            'ucash' => UCashGateway::class,
            'unionpay' => UnionPayGateway::class,
            'upay' => UpayGateway::class,
            'urpay' => UrpayGateway::class,
            'wechatpay' => WeChatPayGateway::class,
            'westernunion' => WesternUnionGateway::class,
            'wise' => WiseGateway::class,
            'worldpay' => WorldpayGateway::class,
        ];
    }

    /**
     * Register every gateway from the 195-country catalog. Catalog entries
     * never override an existing concrete registration; they only add keys
     * that are not already defined (preferring the concrete driver when the
     * catalog references one).
     */
    private function registerCountryGateways(): void
    {
        foreach ($this->catalog->allGatewayKeys() as $key) {
            if (isset($this->gateways[$key])) {
                continue;
            }

            $driver = $this->catalog->driverFor($key);

            if ($driver === null) {
                continue;
            }

            $this->gateways[$key] = $driver;
            $this->gatewayNames[$key] = $this->catalog->gatewayName($key) ?? $key;
        }
    }

    /**
     * Get gateway class.
     */
    private function getGatewayClass(string $gateway): string
    {
        if (isset($this->customGateways[$gateway])) {
            return $this->customGateways[$gateway];
        }

        if (isset($this->gateways[$gateway])) {
            return $this->gateways[$gateway];
        }

        throw new ConfigurationException("Gateway '{$gateway}' not found");
    }
}
