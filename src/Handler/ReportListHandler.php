<?php

/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

class ReportListHandler
{

    public function respond(array $vars)
    {
        global $config;

        $loader = new \Twig\Loader\FilesystemLoader('tpl');
        $twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        $vars['session'] = $_SESSION;

        $vars['breadcrumb'] = [
            '/' => 'Home',
            '/reports/' => 'Reports & Information',
        ];

        echo $twig->render("reports.twig", $vars);
    }
}
