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
class HomepageHandler
{

    public function respond(array $vars)
    {
        global $config;

        $loader = new \Twig\Loader\FilesystemLoader('tpl');
        $twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        $vars['session'] = $_SESSION;

        echo $twig->render("home.twig", $vars);
        // var_dump($_SESSION);
    }
}
