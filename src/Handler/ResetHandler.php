<?php

/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

class ResetHandler
{

    public function respond(array $vars)
    {
        global $config;

        $loader = new \Twig\Loader\FilesystemLoader('tpl');
        $twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        $vars['session'] = $_SESSION;

        if (!$_SESSION['isStaff']) {
            http_send_status(403);
            echo $twig->render("403.twig", $vars);
            return;
        }

        $queueItem = \QueueItem::getSingleItem($vars['idPost']);
        if (is_null($queueItem)) {
            http_send_status(404);
            echo $twig->render("404.twig", $vars);
            return;
        }

        // we have a valid queueitem and we're staff. reset and pass off to the QueueHandler
        $queueItem->reset();
        $handler = new QueueHandler();
        $handler->respond($vars);
    }
}
