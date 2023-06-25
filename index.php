<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP {

    require_once __DIR__ . '/vendor/autoload.php';
    include_once __DIR__ . '/vendor/codecat/nin/nf.php';

    date_default_timezone_set('UTC');

    nf_route("/", "WormRP\Controller\Index.Index");

    nf_route("/login", "WormRP\Controller\User.Login");
    nf_route("/logout", "WormRP\Controller\User.Logout");
    nf_route("/auth", "WormRP\Controller\User.Auth");

    nf_route("/threads", "WormRP\Controller\Threads.Index");
    nf_route("/threads/search", "WormRP\Controller\Threads.Search");

    $defaultConfig = [
        'name' => 'WormRP',
        'debug' => php_sapi_name() == 'cli-server',
        'routing' => [
            'preferRules' => false,
            'rules' => [
                '/^\\/(?<path>[a-z0-9\\-_\\/]+)$/' => "WormRP\Controller\Error.404",
            ],
        ],
        'cache' => [
            'class' => 'APCu',
            'options' => [
                'prefix' => 'wormrp_',
            ],
        ],
        'user' => [
            'model' => 'WormRP\Model\User'
        ],
    ];

    nf_begin(array_merge($defaultConfig, require_once 'config.php'));
}