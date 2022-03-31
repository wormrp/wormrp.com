<?php

/*
 * Copyright (c) 2018 WormFic.net
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

/**
 * Description of BadMethodHandler
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class BadMethodHandler
{

    public function respond(array $vars)
    {
        http_response_code(405);
        $loader = new \Twig\Loader\FilesystemLoader('tpl');
        $twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        echo $twig->render("405.twig", $vars);
    }
}
