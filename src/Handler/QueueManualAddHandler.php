<?php

/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

use Carbon\Carbon;

class QueueManualAddHandler
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

        // todo: better filtering but this is a staff util so w/e
        if (!str_starts_with($_POST['url'], "https://www.reddit.com/r/wormrp/comments/")) {
            echo "bad URL! must be a www.reddit.com/r/wormrp url.";
            die();
        }

        $info = json_decode(file_get_contents($_POST['url'] . ".json"))[0]->data->children[0]->data;
        // have i mentioned i fucking hate reddit's json structure

        $queueItem = new \QueueItem();
        $queueItem->idPost = \Snowflake::generate();
        $queueItem->title = $info->title;
        $queueItem->author = $info->author;
        $queueItem->flair = $info->link_flair_text;
        $queueItem->url = $info->url;
        $queueItem->postTime = Carbon::createFromTimestamp($info->created_utc) ?? Carbon::now();
        $queueItem->create();

        header("Location: /reports/queue");
        $handler = new QueueHandler();
        $handler->respond($vars);
    }
}
