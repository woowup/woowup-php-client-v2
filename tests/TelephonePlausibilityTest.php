<?php

// Run: php tests/TelephonePlausibilityTest.php

// Autoloads src/ directly so the test runs with plain `php`, without composer install.
spl_autoload_register(function ($class) {
    $prefix = 'WoowUpV2\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

use WoowUpV2\DataQuality\TelephoneCleanser;

$passed = 0;
$failed = 0;

function check($cond, $msg)
{
    global $passed, $failed;
    if ($cond) { $passed++; echo "  OK   $msg\n"; }
    else       { $failed++; echo "  FAIL $msg\n"; }
}

$cleanser = new TelephoneCleanser();

$cases = [
    'plausible numbers' => [
        '3011528226'   => true,
        '1143215678'   => true,
        '98745216'     => true,
        '573011528226' => true,
    ],
    'non-digit input' => [
        ''               => false,
        '+3011528226'    => false,
        '301 152 8226'   => false,
        '301-152-8226'   => false,
        '30115282a6'     => false,
    ],
    'repeated runs' => [
        '3011111826' => false,
        '3000005826' => false,
    ],
    'ascending and descending sequences' => [
        '5512345678'  => false,
        '3001234567'  => false,
        '51987654321' => false,
        '3087654321'  => false,
    ],
    'bad lengths' => [
        '3011528'          => false,
        '3011528226301152' => false,
    ],
    'generic placeholders' => [
        '963258741' => false,
        '987456321' => false,
    ],
];

foreach ($cases as $group => $numbers) {
    echo "\n-- $group --\n";
    foreach ($numbers as $digits => $expected) {
        $digits = (string) $digits;
        $got = $cleanser->isPlausible($digits);
        check($got === $expected, "isPlausible('$digits') === " . var_export($expected, true) . " (got " . var_export($got, true) . ")");
    }
}

echo "\n-- sanitize() keeps returning the formatted number --\n";
foreach ([
    '5512345678'  => '+5512345678',
    '51987654321' => '+51987654321',
    '3011528226'  => '3011528226',
    '3001234567'  => false,
    '963258741'   => false,
] as $raw => $expected) {
    $raw = (string) $raw;
    $got = $cleanser->sanitize($raw);
    check($got === $expected, "sanitize('$raw') === " . var_export($expected, true) . " (got " . var_export($got, true) . ")");
}

echo "\n$passed OK, $failed FAIL\n";
exit($failed > 0 ? 1 : 0);
