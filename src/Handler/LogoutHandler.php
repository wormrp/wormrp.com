<?php

/*
 * Copyright (c) 2018 WormFic.net
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

/**
 * Description of NotFoundHandler
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class LogoutHandler
{

    public function respond(array $vars)
    {
        global $config;

        unset(
            $_SESSION['wormrp.com/auth'],
            $_SESSION['discordTag'],
            $_SESSION['discordAv'],
            $_SESSION['discordID'],
            $_SESSION['isStaff']
        );

        $redirect = array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '/';
        header("Location: $redirect");
    }
}
