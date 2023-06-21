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

    nf_route("/", "WormRP\IndexController.Index");
    nf_route("/login", "WormRP\UserController.Login");
    nf_route("/auth", "WormRP\UserController.Auth");

    nf_begin(require_once 'config.php');
}