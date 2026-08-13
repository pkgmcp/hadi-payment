<?php

declare(strict_types=1);

$file = __DIR__ . '/../../src/Data/gateways_by_country.php';
$content = file_get_contents($file);

if ($content === false) {
    fwrite(STDERR, "Cannot read $file\n");
    exit(1);
}

$content = preg_replace(
    "/'driver' => (Hadi\\\\Payment\\\\Gateways\\\\[A-Za-z]+)(\]|,)/",
    "'driver' => \\\\$1::class$2",
    $content
);

file_put_contents($file, $content);
echo "repaired\n";
