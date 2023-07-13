<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Controller;

use Nin\Nin;
use WormRP\Controller;
use WormRP\Model\Post;

class Index extends Controller
{
    public function actionIndex()
    {
        $doots = [];
        if (Nin::user()) {
            foreach (Post::findAllByAttributes(['idPing' => Nin::uid(), 'isDeleted' => false]) as $post) {
                if (count($post->replies) == 0) {
                    $doots[] = $post;
                }
            }
        }
        $this->render("home", ['doots' => $doots]);
    }

    public function actionWikiWizard()
    {
        if (array_key_exists('name', $_REQUEST) && mb_strlen(trim($_REQUEST['name'])) > 0) {
            $name = str_replace(" ", "_", trim($_REQUEST['name']));
            header("Location: https://wiki.wormrp.com/wiki/edit/$name?preload=WormRP:Character_creation_template");
            return;
        }

        $this->render("wikiWizard", []);
    }
}
