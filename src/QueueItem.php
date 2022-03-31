<?php
/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

use Carbon\Carbon;
use CharlotteDunois\Collect\Collection;

class QueueItem
{
    public int $idPost;
    public string $flair;
    public string $author;
    public string $url;
    public Carbon $postTime;
    public Carbon $claimTime;
    public Carbon $approvalTime;
    public int $idApprover1;
    public int $idApprover2;

    public static function getQueue(): Collection
    {
        global $db; // i know
        $query = $db->executeQuery("select * from wormrp_queue");
        $c = new CharlotteDunois\Collect\Collection();
        while ($res = $query->fetchAssociative()) {
            $x = self::createFromDBrow($res);
            $c->set($x->idPost, $x);
        }
        return $c;
    }

    public static function createFromDBrow(array $res): self
    {
        $x = new self();

        foreach ($res as $k => $v) {
            if (is_null($v)) {
                continue;
            }

            $x->$k = match ($k) {
                "postTime", "claimTime", "approvalTime" => new Carbon($v),
                default => $v,
            };
        }

        return $x;
    }
}