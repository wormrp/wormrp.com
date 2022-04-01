<?php
/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

class QueueHandler
{
    public function respond(array $vars)
    {
        global $config, $db; // i know

        $loader = new \Twig\Loader\FilesystemLoader('tpl');
        $twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        $queue = \QueueItem::getQueue();

        $vars['session'] = $_SESSION;
        $vars['queue'] = $queue;

        $vars['breadcrumb'] = [
            '/' => 'Home',
            '/reports/' => 'Reports & Information',
            '/reports/queue' => 'Approval Queue',
        ];

        echo $twig->render("queue.twig", $vars);
    }
}