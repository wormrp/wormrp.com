<?php

/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

use Carbon\Carbon;
use CharlotteDunois\Collect\Collection;

class WikiAuditHandler
{

    public Collection $users;
    public Collection $characters;

    public function __construct()
    {
        global $db; // i know

        $qb = $db->createQueryBuilder();
        $qb->select("*")->from("wormrp_activity");
        $this->users = new Collection();

        foreach ($qb->fetchAllAssociative() as $v) {
            $v['lastSubActivity'] = new Carbon($v['lastSubActivity']);
            $v['redditLink'] = "https://www.reddit.com/user/" . $v['redditName'];
            $v['redditName'] = "/u/" . $v['redditName'];
            $this->users->set(mb_strtolower($v['redditName']), (object)$v);
        }

        $wikiurl = file_get_contents(
            "https://wiki.wormrp.com/w/api.php?action=ask&format=json&api_version=3&query=[[Status::Active]]" .
            "|?Modification%20date|?Author|?Classification|?Reputation%20(Notoriety)|?Criminal%20Status|limit=500"
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
                if (!in_array($k, ['Classification'])) {
                    $x->$k = $x->$k[0] ?? null;
                }
            }
            $this->characters->set($x->name, $x);
        }
        $this->characters = $this->characters->sortCustom(function ($a, $b) {
            return $a->name <=> $b->name;
        });
    }

    public function respond(array $vars)
    {
        $loader = new \Twig\Loader\FilesystemLoader('tpl');
        $twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        $vars['session'] = $_SESSION;

        $vars['breadcrumb'] = [
            '/' => 'Home',
            '/reports/' => 'Reports & Information',
            '/reports/charcheck' => 'Wiki Character Audit',
        ];

        $vars['inactiveUsers'] = $this->inactiveUsers();
        $vars['missingUsers'] = $this->missingUsers();
        $vars['missingRep'] = $this->missingRep();
        $vars['missingClass'] = $this->missingClass();
        $vars['staleWikiPages'] = $this->staleWikiPages();

        echo $twig->render("wikiAudit.twig", $vars);
    }

    protected function inactiveUsers(): Collection
    {
        $cutoff = Carbon::now()->addDays(-14);
        return $this->users->filter(function ($v) use ($cutoff) {
            return $v->lastSubActivity < $cutoff;
        })->sortCustom(function ($a, $b) {
            return $a->lastSubActivity <=> $b->lastSubActivity;
        })->map(function ($v, $k) use (&$x) {
            $mycapes = $this->characters->filter(fn($vv) => mb_strtolower($vv->Author) == $k);
            if ($mycapes->count() == 0) {
                return null;
            }
            return [
                'v' => $v,
                'capes' => $mycapes->map(fn($x) => sprintf('<a href="%s">%s</a>', $x->url, $x->name))->implode(null),
            ];
        })->filter(function ($v) {
            return is_array($v);
        });
    }

    protected function missingUsers(): Collection
    {
        return $this->characters->filter(function ($v) {
            return !$this->users->has(mb_strtolower($v->Author));
        });
    }

    protected function missingRep(): Collection
    {
        return $this->characters->filter(function ($v) {
            return is_null($v->{"Reputation (Notoriety)"});
        });
    }

    protected function missingClass(): Collection
    {
        return $this->characters->filter(function ($v) {
            return count($v->Classification) == 0;
        });
    }

    protected function staleWikiPages(): Collection
    {
        $cutoff = Carbon::now()->addDays(-90);
        return $this->characters->filter(fn($v) => $v->{"Modification date"} < $cutoff)
            ->sortCustom(fn($a, $b) => $a->{"Modification date"} <=> $b->{"Modification date"});
    }
}
