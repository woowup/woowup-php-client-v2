<?php

/**
 * PUT /multiusers answers 400 "No identificator received, please check multi-id settings" when the
 * body carries service_uid and the account does not have it in its multi-id. Users reads the
 * multi-id once per instance with GET /account and drops only service_uid from the body, without
 * touching the caller's UserModel. When the multi-id cannot be read, the body goes as before.
 *
 * Needs Guzzle: it uses vendor/autoload.php when there is one, or the autoloader passed in
 * GUZZLE_AUTOLOAD, and is skipped otherwise.
 *
 * Ejecutar: php tests/UsersUnconfiguredServiceUidTest.php
 */

// Autoloader propio sobre src/: el test corre con `php` plano, sin composer install
// y sin depender de que haya un vendor instalado.
$srcAutoload = function ($class) {
    $prefijo = 'WoowUpV2\\';
    if (strpos($class, $prefijo) !== 0) {
        return;
    }
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefijo))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
};
spl_autoload_register($srcAutoload);

$autoload = getenv('GUZZLE_AUTOLOAD') ?: __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    echo "-- SKIP: sin Guzzle (pasar GUZZLE_AUTOLOAD) --\n";
    exit(0);
}
require_once $autoload;
// El autoload de composer se antepone; si ese vendor trae otra copia de WoowUpV2, gana la de src/.
spl_autoload_unregister($srcAutoload);
spl_autoload_register($srcAutoload, true, true);

use GuzzleHttp\Psr7\Response;
use WoowUpV2\Models\UserModel;

// Los setters avisan con E_USER_WARNING cuando descartan un dato.
set_error_handler(function ($errno) {
    return $errno === E_USER_WARNING;
});

$passed = 0;
$failed = 0;

function check($cond, $msg)
{
    global $passed, $failed;
    if ($cond) { $passed++; echo "  OK   $msg\n"; }
    else       { $failed++; echo "  FAIL $msg\n"; }
}

function mockedClient(array $responses, &$history)
{
    $history = [];
    $stack   = \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler($responses));
    $stack->push(\GuzzleHttp\Middleware::history($history));

    return new \WoowUpV2\Client('apikey', 'https://api.woowup.com', 'apiv3', new \GuzzleHttp\Client(['handler' => $stack]));
}

function accountResponse($settings)
{
    return new Response(200, [], json_encode(['payload' => ['app_id' => 1893, 'multidentificator_settings' => $settings], 'code' => 'ok']));
}

function okResponse()
{
    return new Response(200, [], json_encode(['payload' => [], 'code' => 'ok']));
}

function customer()
{
    $user = new UserModel();

    return $user->setServiceUid('ext-123')
        ->setDocument('30111222')
        ->setEmail('juan@example.com')
        ->setTelephone('+5491155554444');
}

function requestsOf(array $history, $method)
{
    return array_values(array_filter($history, function ($t) use ($method) {
        return $t['request']->getMethod() === $method;
    }));
}

function bodyOf($transaction)
{
    return json_decode((string) $transaction['request']->getBody(), true);
}

$withoutUid = ['document' => ['type' => 'hard'], 'email' => ['type' => 'soft'], 'telephone' => ['type' => 'soft']];
$withUid    = $withoutUid + ['service_uid' => ['type' => 'soft']];

echo "\n-- Account::multidentificatorSettings --\n";
$woowup = mockedClient([accountResponse($withoutUid)], $history);
check($woowup->account->multidentificatorSettings() === $withoutUid, 'devuelve las settings como array');
check((string) $history[0]['request']->getUri() === 'https://api.woowup.com/apiv3/account', 'hace GET a /apiv3/account');
foreach ([
    'vacio {}'      => accountResponse(new \stdClass()),
    'vacio []'      => accountResponse([]),
    'sin settings'  => new Response(200, [], json_encode(['payload' => ['app_id' => 1893]])),
    'no array'      => accountResponse('raro'),
    'body no JSON'  => new Response(200, [], '<html>'),
    '204'           => new Response(204),
] as $name => $response) {
    $woowup = mockedClient([$response], $history);
    check($woowup->account->multidentificatorSettings() === null, "devuelve null: $name");
}

$updates = [
    'update'      => function ($woowup, $user) { return $woowup->users->update($user); },
    'updateAsync' => function ($woowup, $user) { return $woowup->users->updateAsync($user)->wait(); },
];

foreach ($updates as $method => $call) {
    echo "\n-- Users::$method --\n";

    $woowup = mockedClient([accountResponse($withUid), okResponse()], $history);
    $user   = customer();
    $call($woowup, $user);
    $body = bodyOf(requestsOf($history, 'PUT')[0]);
    check(($body['service_uid'] ?? null) === 'ext-123' && $body === json_decode(json_encode($user), true), 'con service_uid en el multi-id: body intacto');

    $woowup = mockedClient([accountResponse($withoutUid), okResponse()], $history);
    $user   = customer();
    $before = serialize($user);
    $call($woowup, $user);
    $put  = requestsOf($history, 'PUT')[0];
    $body = bodyOf($put);
    check((string) $put['request']->getUri() === 'https://api.woowup.com/apiv3/multiusers', 'hace PUT a /apiv3/multiusers');
    check(!array_key_exists('service_uid', $body), 'sin service_uid en el multi-id: el body no lo trae');
    check($body['document'] === '30111222' && $body['email'] === 'juan@example.com' && isset($body['telephone']), 'conserva document, email y telephone');
    check($user->getServiceUid() === 'ext-123' && serialize($user) === $before, 'el UserModel del caller queda intacto');

    $failures = [
        'excepcion'         => new \GuzzleHttp\Exception\ConnectException('timeout', new \GuzzleHttp\Psr7\Request('GET', 'account')),
        '500'               => new Response(500, [], '{"code":"internal_error"}'),
        '404'               => new Response(404, [], '{}'),
        'sin settings'      => new Response(200, [], json_encode(['payload' => ['app_id' => 1893]])),
        'settings vacias'   => accountResponse(new \stdClass()),
    ];
    foreach ($failures as $name => $response) {
        $woowup = mockedClient([$response, okResponse(), okResponse()], $history);
        try {
            $call($woowup, customer());
            $call($woowup, customer());
            $puts = requestsOf($history, 'PUT');
            check(count($puts) === 2 && bodyOf($puts[0])['service_uid'] === 'ext-123' && bodyOf($puts[1])['service_uid'] === 'ext-123', "GET /account $name: body como antes y no tira");
            check(count(requestsOf($history, 'GET')) === 1, "GET /account $name: el fallo no se reintenta en cada update");
        } catch (\Throwable $e) {
            check(false, "GET /account $name: tiro " . get_class($e) . ': ' . $e->getMessage());
        }
    }

    $woowup = mockedClient([accountResponse($withoutUid), okResponse(), okResponse(), okResponse()], $history);
    $call($woowup, customer());
    $call($woowup, customer());
    $call($woowup, customer());
    $puts = requestsOf($history, 'PUT');
    check(count(requestsOf($history, 'GET')) === 1 && count($puts) === 3, 'tres updates en la misma instancia hacen un solo GET /account');
    check(!array_key_exists('service_uid', bodyOf($puts[2])), 'el ultimo PUT tampoco trae service_uid');
}

echo "\n$passed OK, $failed FAIL\n";
exit($failed > 0 ? 1 : 0);
