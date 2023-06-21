<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP;

use Nin\Nin;
use Twig\Environment;

class Controller extends \Nin\Controller
{
    public Environment $twig;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader('tpl');
        $this->twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);
    }

    public function renderPartial($view, $options = [])
    {
        return $this->twig->render($view . ".twig", $options);
    }

    public function render($view, $options = [])
    {
        $this->twig->addGlobal("user", Nin::user());
        echo $this->twig->render($view . ".twig", $options);
    }

}