<?php
/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

session_start(['cookie_lifetime' => 86400 * 7]);
date_default_timezone_set('UTC');

$isTestMode = (PHP_OS == "WINNT");

if (array_key_exists('wormrp.com/auth', $_SESSION)) {
    $provider = new \Wohali\OAuth2\Client\Provider\Discord([
        'clientId' => $config['clientId'],
        'clientSecret' => $config['clientSecret'],
        'redirectUri' => $isTestMode ? 'http://localhost:8000/auth' : 'https://wormrp.com/auth'
    ]);

    if ($_SESSION['wormrp.com/auth']->hasExpired()) {
        $_SESSION['wormrp.com/auth'] = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $_SESSION['wormrp.com/auth']->getRefreshToken()
        ]);
    }
}

$db = \Doctrine\DBAL\DriverManager::getConnection(['url' => $config['db']], new \Doctrine\DBAL\Configuration());

$dispatcher = FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', 'HomepageHandler');
    $r->addRoute('GET', '/reports/', 'ReportListHandler');

    $r->addRoute('GET', '/auth', 'AuthHandler');
    $r->addRoute('GET', '/logout', 'LogoutHandler');

    $r->addRoute('GET', '/map', 'MapHandler');

    $r->addRoute('GET', '/reports/queue', 'QueueHandler');
    $r->addRoute('POST', '/reports/queue/add', 'QueueManualAddHandler');
    $r->addRoute('POST', '/reports/queue/{idPost}/claim', 'ClaimHandler');
    $r->addRoute('POST', '/reports/queue/{idPost}/complete', 'CompleteHandler');
    $r->addRoute('POST', '/reports/queue/{idPost}/reset', 'ResetHandler');

    $r->addRoute('GET', '/reports/charcheck', 'WikiAuditHandler');
    $r->addRoute('GET', '/reports/monthly', 'MonthlyAccountingHandler');
    $r->addRoute('GET', '/reports/wikiwizard', 'WikiWizardHandler');
});

$uri = $_SERVER['REQUEST_URI'];
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
$routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $vars = ['uri' => $uri];
        $handler = new Handler\NotFoundHandler();
        $handler->respond($vars);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // TODO: return json as needed?
        $vars = ['allowedMethods' => $routeInfo[1]];
        $handler = new Handler\BadMethodHandler();
        $handler->respond($vars);
        break;
    case FastRoute\Dispatcher::FOUND:
        $hname = "Handler\\{$routeInfo[1]}";
        $handler = new $hname();
        $handler->respond($routeInfo[2]);
        break;
}
