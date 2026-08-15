<?php

declare(strict_types=1);

namespace Hadi\Payment\Tests;

use Hadi\Payment\Gateways\Generic\GenericGateway;
use Hadi\Payment\Services\CountryCatalog;

class CountryCatalogTest extends TestCase
{
    public function test_it_covers_all_195_countries(): void
    {
        $catalog = app(CountryCatalog::class);

        $this->assertCount(195, $catalog->all());
    }

    public function test_every_country_has_at_least_three_gateways(): void
    {
        $catalog = app(CountryCatalog::class);

        foreach ($catalog->all() as $iso => $country) {
            $count = count($country['gateways']);
            $this->assertGreaterThanOrEqual(
                3,
                $count,
                "{$iso} ({$country['name']}) has only {$count} gateways"
            );
        }
    }

    public function test_catalog_contains_key_world_markets(): void
    {
        $catalog = app(CountryCatalog::class);

        $this->assertNotNull($catalog->gatewaysFor('BD'));
        $this->assertNotNull($catalog->gatewaysFor('US'));
        $this->assertNotNull($catalog->gatewaysFor('NG'));
        $this->assertNotNull($catalog->gatewaysFor('BR'));
    }

    public function test_catalog_contains_1900_plus_unique_gateways(): void
    {
        $catalog = app(CountryCatalog::class);

        $unique = array_unique($catalog->allGatewayKeys());

        $this->assertGreaterThanOrEqual(1900, count($unique));
    }

    public function test_catalog_contains_25000_plus_total_entries(): void
    {
        $catalog = app(CountryCatalog::class);

        $total = array_sum(array_map(
            fn (array $country) => count($country['gateways']),
            $catalog->all()
        ));

        $this->assertGreaterThanOrEqual(25000, $total);
    }

    public function test_gateways_resolve_to_driver_classes(): void
    {
        $catalog = app(CountryCatalog::class);

        foreach ($catalog->gatewaysFor('BD') ?? [] as $gateway) {
            $isValid = is_subclass_of($gateway['driver'], GenericGateway::class)
                || is_a($gateway['driver'], \Hadi\Payment\PaymentGateway::class, true);

            $this->assertTrue(
                $isValid,
                "Driver {$gateway['driver']} for {$gateway['key']} is invalid"
            );
        }
    }

    public function test_unknown_country_returns_null(): void
    {
        $catalog = app(CountryCatalog::class);

        $this->assertNull($catalog->gatewaysFor('ZZ'));
        $this->assertNull($catalog->country('ZZ'));
    }

    public function test_gateway_index_is_unique_and_searchable(): void
    {
        $catalog = app(CountryCatalog::class);
        $keys = $catalog->allGatewayKeys();

        $this->assertSame($keys, array_unique($keys));

        $bkash = $catalog->findGateway('bkash');
        $this->assertNotNull($bkash);
        $this->assertSame('Bangladesh', $bkash['country']);
        $this->assertTrue($catalog->hasGateway('bkash'));
    }

    public function test_gateway_name_and_driver_are_available(): void
    {
        $catalog = app(CountryCatalog::class);

        $this->assertSame('MTN Mobile Money', $catalog->gatewayName('mtn-momo'));
        $this->assertNotNull($catalog->driverFor('mtn-momo'));
        $this->assertNull($catalog->driverFor('does-not-exist'));
    }

    public function test_every_country_has_curated_top_list_covering_all_rails(): void
    {
        $catalog = app(CountryCatalog::class);

        foreach ($catalog->all() as $iso => $country) {
            $top = $catalog->topGatewaysFor($iso);
            $this->assertNotNull($top, "{$iso} is missing a top list");
            $this->assertGreaterThanOrEqual(4, count($top), "{$iso} top list has fewer than 4 gateways");
            $this->assertLessThanOrEqual(5, count($top), "{$iso} top list has more than 5 gateways");

            $types = array_column($top, 'type');

            foreach (['mobile', 'card', 'bank', 'virtual_card'] as $required) {
                $this->assertContains(
                    $required,
                    $types,
                    "{$iso} top list lacks a {$required} gateway"
                );
            }
        }
    }

    public function test_top_gateways_resolve_to_drivers_and_are_catalog_members(): void
    {
        $catalog = app(CountryCatalog::class);

        foreach ($catalog->all() as $iso => $country) {
            foreach ($catalog->topGatewaysFor($iso) ?? [] as $gateway) {
                $isValid = is_subclass_of($gateway['driver'], GenericGateway::class)
                    || is_a($gateway['driver'], \Hadi\Payment\PaymentGateway::class, true);

                $this->assertTrue($isValid, "{$iso} top gateway {$gateway['key']} driver is invalid");
                $this->assertTrue(
                    $catalog->hasGateway($gateway['key']),
                    "{$iso} top gateway {$gateway['key']} missing from the catalog"
                );
            }
        }
    }

    public function test_top_keys_for_returns_slugs(): void
    {
        $catalog = app(CountryCatalog::class);

        $keys = $catalog->topKeysFor('US');
        $this->assertNotNull($keys);
        $this->assertCount(5, $keys);
        $this->assertSame($keys, array_map('strtolower', $keys));
        $this->assertNull($catalog->topKeysFor('ZZ'));
    }

    public function test_known_gateways_resolve_to_concrete_drivers(): void
    {
        $catalog = app(CountryCatalog::class);

        $expected = [
            'bkash' => \Hadi\Payment\Gateways\BkashGateway::class,
            'banglaqr' => \Hadi\Payment\Gateways\BanglaQrGateway::class,
            'stripe' => \Hadi\Payment\Gateways\StripeGateway::class,
            'paypal' => \Hadi\Payment\Gateways\PayPalGateway::class,
            'm-pesa' => \Hadi\Payment\Gateways\MpesaGateway::class,
            'amarpay' => \Hadi\Payment\Gateways\AmarPayGateway::class,
            'bluesnap' => \Hadi\Payment\Gateways\BlueSnapGateway::class,
            'sagepay' => \Hadi\Payment\Gateways\SagePayGateway::class,
            'peachpayments' => \Hadi\Payment\Gateways\PeachPaymentsGateway::class,
            'privacy-com' => \Hadi\Payment\Gateways\PrivacyComGateway::class,
        ];

        foreach ($expected as $slug => $class) {
            $this->assertSame(
                $class,
                $catalog->driverFor($slug),
                "{$slug} should resolve to {$class}"
            );
        }
    }

    public function test_country_metadata_includes_top_list(): void
    {
        $catalog = app(CountryCatalog::class);

        $us = $catalog->country('US');
        $this->assertNotNull($us);
        $this->assertArrayHasKey('top', $us);
        $this->assertGreaterThanOrEqual(4, count($us['top']));
    }
}
