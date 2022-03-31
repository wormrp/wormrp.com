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

        $queue = \QueueItem::getQueue();

        var_dump($queue->first());
    }
}