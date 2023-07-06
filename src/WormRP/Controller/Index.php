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
}
