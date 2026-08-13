<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Catalog Generator
|--------------------------------------------------------------------------
|
| Regenerates src/Data/gateways_by_country.php from the master gateway
| database (scripts/catalog/master_gateways.php) combined with the
| country-specific pool (scripts/catalog/country_gateways.php).
|
|   php scripts/catalog/generate_catalog.php
|
| Every country keeps its existing curated entries (and any concrete driver
| references) first, then receives country-pool gateways, then the gateways
| scoped to its region, then the globally available gateways. Concrete
| driver classes referenced by the existing catalog or the country pool are
| preserved as fully qualified imports.
|
*/

$root = dirname(__DIR__, 2);

$data = require $root . '/src/Data/gateways_by_country.php';
$master = require $root . '/scripts/catalog/master_gateways.php';
$pool = require $root . '/scripts/catalog/country_gateways.php';
$drivers = require $root . '/scripts/catalog/drivers.php';

/*
| Build lookup structures from the master database.
| scope => global | countries, or a region key names the region.
*/
$global = [];
$regional = [];
$countryScoped = [];

foreach ($master as $slug => $entry) {
    $scope = $entry['scope'] ?? null;

    if ($scope === 'global') {
        $global[$slug] = $entry;
    } elseif ($scope === 'countries' && isset($entry['countries'])) {
        foreach ($entry['countries'] as $iso) {
            $countryScoped[strtoupper($iso)][$slug] = $entry;
        }
    } elseif (isset($entry['region'])) {
        $regional[$entry['region']][$slug] = $entry;
    }
}

/*
| Collect every concrete driver referenced by the existing catalog entries
| so the regenerated file keeps the exact same `use` imports.
*/
$driverBySlug = [];
$driverClasses = [];

foreach ($data as $country) {
    foreach ($country['gateways'] as $gateway) {
        if (isset($gateway['driver'])) {
            $driverBySlug[$gateway['key']] = $gateway['driver'];
            $driverClasses[$gateway['driver']] = true;
        }
    }
}

/*
| Authoritative type overrides: when the master database or the country pool
| defines a slug with a specific type (e.g. mobile money apps typed as
| `mobile`), that type wins over whatever the existing catalog carried.
*/
$typeOverride = [];

foreach ($master as $slug => $entry) {
    if (isset($entry['type'])) {
        $typeOverride[$slug] = $entry['type'];
    }
}

foreach ($pool as $slugs) {
    foreach ($slugs as $slug => $entry) {
        if (isset($entry['type'])) {
            $typeOverride[$slug] = $entry['type'];
        }
    }
}

foreach ($pool as $slugs) {
    foreach ($slugs as $slug => $entry) {
        if (isset($entry['driver'])) {
            $driverBySlug[$slug] = $entry['driver'];
            $driverClasses[$entry['driver']] = true;
        }
    }
}

foreach ($master as $slug => $entry) {
    if (isset($entry['driver'])) {
        $driverBySlug[$slug] = $entry['driver'];
        $driverClasses[$entry['driver']] = true;
    }
}

/*
| Concrete driver mapping (drivers.php) takes precedence: any slug with a
| concrete driver class resolves to it regardless of whether the master entry
| or country pool carries one.
*/
foreach ($drivers as $slug => $class) {
    $driverBySlug[$slug] = $class;
    $driverClasses[$class] = true;
}

/*
| Build a curated per-country "top" list: 4-5 gateways that together cover
| mobile money, card, bank transfer and a virtual card rail. Entries earlier
| in the assembled list (curated first, then pool, regional, global) win, so
| a country's own gateways are preferred over generic global ones. Concrete
| driver entries are boosted so the top list showcases integration depth.
*/
function buildTopList(array $gateways): array
{
    $ranked = [];
    $seen = [];

    foreach ($gateways as $i => $gateway) {
        $key = $gateway['key'];

        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;

        $score = -$i;
        $score += isset($gateway['driver']) ? 1000 : 0;

        $ranked[] = [$score, $gateway];
    }

    usort($ranked, static function (array $a, array $b): int {
        return $b[0] <=> $a[0];
    });

    $picked = [];
    $pickedTypes = [];
    $required = ['mobile', 'card', 'bank', 'virtual_card'];

    foreach ($required as $type) {
        foreach ($ranked as $idx => [, $gateway]) {
            if (($gateway['type'] ?? '') === $type && !isset($pickedTypes[$type])) {
                $picked[] = $gateway;
                $pickedTypes[$type] = true;
                unset($ranked[$idx]);
                break;
            }
        }
    }

    foreach ($ranked as [, $gateway]) {
        if (count($picked) >= 5) {
            break;
        }

        $type = $gateway['type'];

        if (isset($pickedTypes[$type]) && count($picked) >= 4) {
            continue;
        }

        $picked[] = $gateway;
        $pickedTypes[$type] = true;
    }

    return array_slice($picked, 0, 5);
}

/*
| Assemble each country's gateway list.
| Existing entries first (keeps curated order + concrete drivers), then the
| country pool, then region scoped gateways, then global gateways.
*/
$out = [];

foreach ($data as $iso => $country) {
    $gateways = [];

    foreach ($country['gateways'] as $gateway) {
        $gateway['type'] = $typeOverride[$gateway['key']] ?? $gateway['type'];
        $gateways[$gateway['key']] = $gateway;
    }

    foreach ($pool[$iso] ?? [] as $slug => $entry) {
        if (isset($gateways[$slug])) {
            continue;
        }

        $gateways[$slug] = ['key' => $slug, 'name' => $entry['name'], 'type' => $entry['type']];
    }

    foreach ($countryScoped[$iso] ?? [] as $slug => $entry) {
        if (isset($gateways[$slug])) {
            continue;
        }

        $gateways[$slug] = ['key' => $slug, 'name' => $entry['name'], 'type' => $entry['type']];
    }

    foreach ($regional[$country['region']] ?? [] as $slug => $entry) {
        if (isset($gateways[$slug])) {
            continue;
        }

        $gateways[$slug] = ['key' => $slug, 'name' => $entry['name'], 'type' => $entry['type']];
    }

    foreach ($global as $slug => $entry) {
        if (isset($gateways[$slug])) {
            continue;
        }

        $gateways[$slug] = ['key' => $slug, 'name' => $entry['name'], 'type' => $entry['type']];
    }

    $resolved = [];

    foreach ($gateways as $slug => $gateway) {
        if (isset($driverBySlug[$slug])) {
            $gateway['driver'] = $driverBySlug[$slug];
        }

        $resolved[] = $gateway;
    }

    $out[$iso] = $country;
    $out[$iso]['gateways'] = $resolved;
    $out[$iso]['top'] = buildTopList($resolved);
}

/*
| Render the data file.
*/
$uses = array_keys($driverClasses);
sort($uses, SORT_STRING);

$lines = [];
$lines[] = '<?php';
$lines[] = '';
$lines[] = 'declare(strict_types=1);';
$lines[] = '';

foreach ($uses as $class) {
    $lines[] = 'use ' . $class . ';';
}

$lines[] = '';
$lines[] = '/*';
$lines[] = '|--------------------------------------------------------------------------';
$lines[] = '| Hadi Payment — Gateways By Country';
$lines[] = '|--------------------------------------------------------------------------';
$lines[] = '|';
$lines[] = '| Auto-generated by scripts/catalog/generate_catalog.php. Do not edit';
$lines[] = '| by hand. Edit scripts/catalog/master_gateways.php and';
$lines[] = '| scripts/catalog/country_gateways.php, then re-run the generator.';
$lines[] = '|';
$lines[] = '| Catalog of payment gateways for all 195 countries. Every country';
$lines[] = '| lists at least three gateways. Entries carry:';
$lines[] = '|';
$lines[] = '|   - key    : gateway slug (used to resolve the driver)';
$lines[] = '|   - name   : human readable gateway name';
$lines[] = '|   - type   : redirect | api | mobile | bank | card | crypto | wallet | virtual_card';
$lines[] = '|   - driver : optional concrete driver class; when omitted the';
$lines[] = '|              gateway is served by the generic driver matching its';
$lines[] = '|              type.';
$lines[] = '|';
$lines[] = '| Each country also carries a curated "top" list of 4-5 gateways';
$lines[] = '| covering mobile money, card, bank transfer and a virtual card';
$lines[] = '| rail.';
$lines[] = '|';
$lines[] = '*/';
$lines[] = '';
$lines[] = 'return [';
$lines[] = '';

foreach ($out as $iso => $country) {
    $lines[] = '    // ' . str_repeat('-', 20) . ' ' . $country['name'];
    $lines[] = '    ' . var_export($iso, true) . ' => [';
    $lines[] = "        'name' => " . var_export($country['name'], true) . ',';
    $lines[] = "        'iso3' => " . var_export($country['iso3'], true) . ',';
    $lines[] = "        'region' => " . var_export($country['region'], true) . ',';
    $lines[] = "        'currency' => " . var_export($country['currency'], true) . ',';
    $lines[] = "        'gateways' => [";

    foreach ($country['gateways'] as $gateway) {
        $parts = [];
        $parts[] = "'key' => " . var_export($gateway['key'], true);
        $parts[] = "'name' => " . var_export($gateway['name'], true);
        $parts[] = "'type' => " . var_export($gateway['type'], true);

        if (isset($gateway['driver'])) {
            $short = substr((string) $gateway['driver'], (int) strrpos((string) $gateway['driver'], '\\') + 1);
            $parts[] = "'driver' => " . $short . '::class';
        }

        $lines[] = '            [' . implode(', ', $parts) . '],';
    }

    $lines[] = '        ],';
    $lines[] = '        ' . var_export('top', true) . ' => [';

    foreach ($country['top'] as $gateway) {
        $parts = [];
        $parts[] = "'key' => " . var_export($gateway['key'], true);
        $parts[] = "'name' => " . var_export($gateway['name'], true);
        $parts[] = "'type' => " . var_export($gateway['type'], true);

        if (isset($gateway['driver'])) {
            $short = substr((string) $gateway['driver'], (int) strrpos((string) $gateway['driver'], '\\') + 1);
            $parts[] = "'driver' => " . $short . '::class';
        }

        $lines[] = '            [' . implode(', ', $parts) . '],';
    }

    $lines[] = '        ],';
    $lines[] = '    ],';
    $lines[] = '';
}

$lines[] = '];';
$lines[] = '';

$file = $root . '/src/Data/gateways_by_country.php';

if (file_put_contents($file, implode("\n", $lines)) === false) {
    fwrite(STDERR, "Unable to write $file\n");
    exit(1);
}

/*
| Validate the generated catalog.
*/
$generated = require $file;

$unique = [];
$total = 0;
$min = PHP_INT_MAX;
$minCountries = [];
$topShort = [];
$requiredTopTypes = ['mobile', 'card', 'bank', 'virtual_card'];

foreach ($generated as $iso => $country) {
    $count = count($country['gateways']);
    $total += $count;
    $min = min($min, $count);

    if ($count === $min) {
        $minCountries[] = $iso;
    }

    foreach ($country['gateways'] as $gateway) {
        $unique[$gateway['key']] = true;
    }

    $top = $country['top'] ?? [];

    if (count($top) < 4 || count($top) > 5) {
        $topShort[] = $iso . ':' . count($top);
    }

    $topTypes = [];

    foreach ($top as $gateway) {
        $topTypes[$gateway['type']] = true;
    }

    foreach ($requiredTopTypes as $type) {
        if (!isset($topTypes[$type])) {
            $topShort[] = $iso . ':missing:' . $type;
        }
    }
}

$uniqueCount = count($unique);

printf("Countries : %d\n", count($generated));
printf("Entries   : %d\n", $total);
printf("Unique    : %d\n", $uniqueCount);
printf("Min/country: %d (%s)\n", $min, implode(',', $minCountries));
printf("Avg/country: %.1f\n", $total / count($generated));

if (count($generated) !== 195) {
    fwrite(STDERR, "Expected 195 countries.\n");
    exit(1);
}

if ($uniqueCount < 900) {
    fwrite(STDERR, "Expected 900+ unique gateways, got $uniqueCount.\n");
    exit(1);
}

if ($min < 3) {
    fwrite(STDERR, "Every country must list at least 3 gateways.\n");
    exit(1);
}

if ($topShort !== []) {
    fwrite(STDERR, 'Top lists must have 4-5 gateways covering mobile, card, bank and virtual_card. Issues: ' . implode(', ', $topShort) . "\n");
    exit(1);
}

echo "OK: src/Data/gateways_by_country.php regenerated and validated.\n";
