<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP {

    use Carbon\Carbon;

    require_once __DIR__ . '/vendor/autoload.php';
    include_once __DIR__ . '/nin/nf.php';

    date_default_timezone_set('UTC');
    define("TIME_INIT", Carbon::now());

    if (file_exists(__DIR__ . "/GIT_DESCRIBE")) {
        define("GIT_DESCRIBE", trim(file_get_contents(__DIR__ . "/GIT_DESCRIBE")));
    } else {
        define("GIT_DESCRIBE", trim(`git describe --tags --dirty --always`));
    }
    if (file_exists(__DIR__ . "/GIT_HASH")) {
        define("GIT_HASH", trim(file_get_contents(__DIR__ . "/GIT_HASH")));
    } else {
        define("GIT_HASH", trim(`git rev-parse head`));
    }

    nf_route("/", "WormRP\Controller\Index.Index");

    nf_route("/login", "WormRP\Controller\User.Login");
    nf_route("/logout", "WormRP\Controller\User.Logout");
    nf_route("/auth", "WormRP\Controller\User.Auth");

    nf_route("/threads", "WormRP\Controller\Threads.Index");
    nf_route("/threads/search", "WormRP\Controller\Threads.Search");
    nf_route("/threads/new", "WormRP\Controller\Threads.New");

    nf_route("/thread/:idThread", "WormRP\Controller\Thread.View");
    nf_route("/thread/:idThread/edit", "WormRP\Controller\Thread.Edit");
    nf_route("/thread/:idThread/reply", "WormRP\Controller\Thread.Reply");
    nf_route("/thread/:idThread/edit/:idPost", "WormRP\Controller\Thread.EditReply");
    nf_route("/thread/:idThread/delete/:idPost", "WormRP\Controller\Thread.DeleteReply");

    nf_route("/characters", "WormRP\Controller\Characters.Index");
    nf_route("/characters/new", "WormRP\Controller\Characters.New");
    nf_route("/characters/queue", "WormRP\Controller\Characters.Approvals");

    nf_route("/character/:idChar/avatar", "WormRP\Controller\Character.Avatar");
    nf_route("/character/:idChar/setavatar", "WormRP\Controller\Character.SetAvatar");

    nf_route("/admin/users", "WormRP\Controller\Admin.UserList");
    nf_route("/admin/users/:idUser", "WormRP\Controller\Admin.UserFlags");

    nf_route("/api/deploy", "WormRP\Controller\API.DeployWebsite");

    $defaultConfig = [
        'name' => 'WormRP',
        'debug' => php_sapi_name() == 'cli-server' || file_exists(__DIR__ . "/DEBUG"),
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

    $mdParser = new \ParsedownExtra();
    $mdParser->setSafeMode(true);

    $cfg = array_merge($defaultConfig, require_once 'config.php');
    nf_config_initialize($cfg);
    nf_db_initialize();

    session_set_save_handler(new DatabaseSessionHandler(), true);
    session_start([
        'name' => 'wormrp_session',
        'cookie_lifetime' => 86400 * 7,
        'gc_maxlifetime' => 86400 * 7,
        'use_strict_mode' => true,
        'cookie_secure' => php_sapi_name() != 'cli-server',
        'cookie_samesite' => 'Lax',
        'lazy_write' => false,
    ]);

    nf_begin($cfg);
}