<?php

/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

class WikiWizardHandler
{

    public function respond(array $vars)
    {
        global $config;

        if (array_key_exists('name', $_REQUEST) && mb_strlen(trim($_REQUEST['name'])) > 0) {
            $name = str_replace(" ", "_", trim($_REQUEST['name']));
            header("Location: https://wiki.wormrp.com/wiki/edit/$name?preload=WormRP:Character_creation_template");
            die();
        }

        $loader = new \Twig\Loader\FilesystemLoader('tpl');
        $twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        $vars['session'] = $_SESSION;

        echo $twig->render("wikiWizard.twig", $vars);
        // var_dump($_SESSION);
    }
}
