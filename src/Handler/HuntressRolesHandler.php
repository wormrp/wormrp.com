<?php

/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

use Carbon\Carbon;
use CharlotteDunois\Collect\Collection;

class HuntressRolesHandler
{

    public Collection $characters;
    public Collection $users;
    public array $roles;

    public function __construct()
    {
        global $db; // i know

        $q = $db->query(
            'SELECT * from wormrp_activity right join wormrp_users on wormrp_users.redditName = wormrp_activity.redditName where wormrp_users.user is not null'
        );
        $this->users = new Collection();

        foreach ($q->fetchAllAssociative() as $v) {
            $v['lastSubActivity'] = new Carbon($v['lastSubActivity']);
            $v['redditLink'] = "https://www.reddit.com/user/" . $v['redditName'];
            $v['redditName'] = "/u/" . $v['redditName'];
            $this->users->set($v['user'], (object)$v);
        }

        $wikiurl = file_get_contents(
            "https://wiki.wormrp.com/w/api.php?action=ask&format=json&api_version=3&query=[[Status::Active]]" .
            "|?Author|?Alignment|?Affiliation|limit=500"
        );

        $items = json_decode($wikiurl)->query->results;

        $this->characters = new Collection();
        foreach ($items as $item) {
            key($item);
            $item = current($item);
            $x = new \stdClass();
            $x->name = $item->fulltext;
            $x->url = $item->fullurl;

            foreach ($item->printouts as $k => $v) {
                $x->$k = [];
                foreach ($v as $vals) {
                    if (is_string($vals)) {
                        $x->$k[] = $vals;
                    } elseif (is_object($vals)) {
                        if (property_exists($vals, "fulltext")) {
                            $x->$k[] = $vals->fulltext;
                        } elseif (property_exists($vals, "timestamp")) {
                            $x->$k[] = Carbon::createFromTimestamp($vals->timestamp);
                        } else {
                            $x->$k[] = $vals->fulltext ?? null;
                        }
                    }
                }
                if (!in_array($k, ['Alignment', 'Affiliation'])) {
                    $x->$k = $x->$k[0] ?? null;
                }
            }
            $this->characters->set($x->name, $x);
        }
        $this->characters = $this->characters->sortCustom(function ($a, $b) {
            return $a->name <=> $b->name;
        });

        $this->roles = [];
        $this->roles["Players"] = "785375824131653632";
        $roleurl = file_get_contents(
            "https://wiki.wormrp.com/w/api.php?action=ask&format=json&api_version=3&query=[[Discord%20role%20ID::%2B]]|?Discord%20role%20ID|limit=500"
        );

        $items = json_decode($roleurl)->query->results;
        foreach ($items as $item) {
            key($item);
            $item = current($item);
            $name = $item->fulltext;
            $id = $item->printouts->{"Discord role ID"}[0];
            $this->roles[$name] = $id;
        }
    }

    public function respond(array $vars)
    {
        header("Content-Type: application/json");

        $x = $this->users->map(function ($u) {
            $roles = [];
            $roles["Players"] = "Players";

            // get this user's active characters
            $chars = $this->characters->filter(fn($v) => ($v->Author == $u->redditName));
            if ($chars->count() == 0) {
                return array_keys($roles);
            }

            foreach ($chars as $v) {
                foreach ($v->Alignment as $a) {
                    $roles[$a] = 1;
                }
                foreach ($v->Affiliation as $a) {
                    if ($a != "None") {
                        $roles[$a] = 1;
                    }
                }
            }

            return array_keys($roles);
        });

        echo json_encode(['roles' => $this->roles, 'data' => $x->all()], JSON_PRETTY_PRINT);
    }
}
