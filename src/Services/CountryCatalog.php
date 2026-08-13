<?php

declare(strict_types=1);

namespace Hadi\Payment\Services;

use Hadi\Payment\Gateways\Generic\BankTransferGateway;
use Hadi\Payment\Gateways\Generic\CardGateway;
use Hadi\Payment\Gateways\Generic\CryptoGateway;
use Hadi\Payment\Gateways\Generic\GenericGateway;
use Hadi\Payment\Gateways\Generic\HostedRedirectGateway;
use Hadi\Payment\Gateways\Generic\MobileMoneyGateway;
use Hadi\Payment\Gateways\Generic\RestApiGateway;
use Hadi\Payment\Gateways\Generic\WalletGateway;

/**
 * Catalog of the top payment gateways per country (all 195 countries).
 *
 * Each catalog entry stores the gateway slug, display name, and type. The
 * type maps to a generic driver class; concrete drivers take precedence when
 * provided on the entry. Every country also carries a curated "top" list of
 * 4-5 gateways covering mobile money, card, bank transfer and a virtual card
 * rail.
 */
class CountryCatalog
{
    /**
     * Map of gateway type => generic driver class.
     *
     * @var array<string, class-string<GenericGateway>>
     */
    private const TYPE_DRIVERS = [
        'redirect' => HostedRedirectGateway::class,
        'api' => RestApiGateway::class,
        'mobile' => MobileMoneyGateway::class,
        'bank' => BankTransferGateway::class,
        'card' => CardGateway::class,
        'crypto' => CryptoGateway::class,
        'wallet' => WalletGateway::class,
        'virtual_card' => CardGateway::class,
    ];

    /**
     * @var array<string, array{name: string, iso3: string, region: string, currency: string, gateways: array<int, array{key: string, name: string, type: string, driver?: class-string}>, top: array<int, array{key: string, name: string, type: string, driver?: class-string}>}>
     */
    private array $countries;

    /**
     * @var array<string, array{country: string, iso: string, name: string, type: string, driver: class-string}>
     */
    private array $gatewayIndex;

    public function __construct()
    {
        $data = require __DIR__ . '/../Data/gateways_by_country.php';
        $this->countries = $data;
        $this->gatewayIndex = $this->buildGatewayIndex($data);
    }

    /**
     * Get all countries keyed by ISO2 code.
     */
    public function all(): array
    {
        return $this->countries;
    }

    /**
     * Number of countries in the catalog.
     */
    public function count(): int
    {
        return count($this->countries);
    }

    /**
     * Get the top gateways for a country by ISO2 code.
     *
     * @return array<int, array{key: string, name: string, type: string, driver: class-string}>|null
     */
    public function gatewaysFor(string $iso): ?array
    {
        $iso = strtoupper($iso);

        if (!isset($this->countries[$iso])) {
            return null;
        }

        return array_map(
            fn (array $entry) => $this->resolveEntry($entry),
            $this->countries[$iso]['gateways']
        );
    }

    /**
     * Get a single country's metadata.
     *
     * @return array{name: string, iso3: string, region: string, currency: string, top: array<int, array{key: string, name: string, type: string}>}|null
     */
    public function country(string $iso): ?array
    {
        $iso = strtoupper($iso);

        if (!isset($this->countries[$iso])) {
            return null;
        }

        return array_filter(
            $this->countries[$iso],
            fn (string $key) => $key !== 'gateways',
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * List of supported countries as ISO2 => name.
     */
    public function countries(): array
    {
        return array_map(fn (array $c) => $c['name'], $this->countries);
    }

    /**
     * The curated top gateways for a country, resolved to full definitions.
     *
     * Every country's top list holds 4-5 gateways covering mobile money,
     * card, bank transfer and a virtual card rail.
     *
     * @return array<int, array{key: string, name: string, type: string, driver: class-string}>|null
     */
    public function topGatewaysFor(string $iso): ?array
    {
        $iso = strtoupper($iso);

        if (!isset($this->countries[$iso])) {
            return null;
        }

        return array_map(
            fn (array $entry) => $this->resolveEntry($entry),
            $this->countries[$iso]['top']
        );
    }

    /**
     * The gateway keys in a country's curated top list.
     *
     * @return array<int, string>|null
     */
    public function topKeysFor(string $iso): ?array
    {
        $iso = strtoupper($iso);

        if (!isset($this->countries[$iso])) {
            return null;
        }

        return array_map(
            fn (array $entry) => strtolower($entry['key']),
            $this->countries[$iso]['top']
        );
    }

    /**
     * Get the driver class for a gateway key.
     */
    public function driverFor(string $gateway): ?string
    {
        $gateway = strtolower($gateway);

        return $this->gatewayIndex[$gateway]['driver'] ?? null;
    }

    /**
     * Get the metadata for a gateway key.
     *
     * @return array{country: string, iso: string, name: string, type: string, driver: class-string}|null
     */
    public function findGateway(string $gateway): ?array
    {
        $gateway = strtolower($gateway);

        return $this->gatewayIndex[$gateway] ?? null;
    }

    /**
     * Whether a gateway key is part of the country catalog.
     */
    public function hasGateway(string $gateway): bool
    {
        return isset($this->gatewayIndex[strtolower($gateway)]);
    }

    /**
     * All catalog gateway keys.
     *
     * @return array<int, string>
     */
    public function allGatewayKeys(): array
    {
        return array_keys($this->gatewayIndex);
    }

    /**
     * The display name for a catalog gateway key.
     */
    public function gatewayName(string $gateway): ?string
    {
        return $this->gatewayIndex[strtolower($gateway)]['name'] ?? null;
    }

    /**
     * Number of catalog gateways.
     */
    public function gatewayCount(): int
    {
        return count($this->gatewayIndex);
    }

    /**
     * Resolve a raw catalog entry into a fully qualified gateway definition.
     *
     * @param array{key: string, name: string, type: string, driver?: class-string} $entry
     * @return array{key: string, name: string, type: string, driver: class-string}
     */
    private function resolveEntry(array $entry): array
    {
        $driver = $entry['driver'] ?? self::TYPE_DRIVERS[$entry['type']] ?? RestApiGateway::class;

        return [
            'key' => strtolower($entry['key']),
            'name' => $entry['name'],
            'type' => $entry['type'],
            'driver' => $driver,
        ];
    }

    /**
     * Build a flat gateway-key => metadata index.
     *
     * @param array<string, array{name: string, iso3: string, region: string, currency: string, gateways: array<int, array{key: string, name: string, type: string, driver?: class-string}>}> $data
     * @return array<string, array{country: string, iso: string, name: string, type: string, driver: class-string}>
     */
    private function buildGatewayIndex(array $data): array
    {
        $index = [];

        foreach ($data as $iso => $country) {
            foreach ($country['gateways'] as $entry) {
                $resolved = $this->resolveEntry($entry);

                $index[$resolved['key']] = [
                    'country' => $country['name'],
                    'iso' => $iso,
                    'name' => $resolved['name'],
                    'type' => $resolved['type'],
                    'driver' => $resolved['driver'],
                ];
            }
        }

        return $index;
    }
}
