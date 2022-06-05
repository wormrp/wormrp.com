<?php

/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

use CharlotteDunois\Collect\Collection;

class MonthlyAccountingHandler extends WikiAuditHandler
{

    public function respond(array $vars)
    {
        $characters = $this->characters->sortCustom(function ($a, $b) {
            return $a->name <=> $b->name;
        })->groupBy("Author")->sortKey(false, SORT_NATURAL | SORT_FLAG_CASE);

        header("Content-Type: text/plain;charset=utf-8");
        foreach ($characters as $u => $c) {
            $user = $this->users->get(mb_strtolower($u));
            $capes = (new Collection($c))->map(function ($v) {
                $x = sprintf("%s (%s%s)", $v->name, $v->{"Reputation (Notoriety)"}, $v->{"Criminal Status"});
                return str_replace("*", "\\*", $x);
            });
            echo sprintf("%s (<@%s>)\n%s\n\n", $u, $user->user, $capes->implode(null, " \u{00B7} "));
        }
    }
}
