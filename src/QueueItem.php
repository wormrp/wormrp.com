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
    public const STATE_PENDING = 0;
    public const STATE_CLAIMED = 1;
    public const STATE_APPROVED = 2;

    public int $idPost;
    public string $flair;
    public string $author;
    public string $url;
    public string $title;
    public Carbon $postTime;
    public ?Carbon $claimTime = null;
    public ?Carbon $approvalTime = null;
    public ?int $idApprover1 = null;
    public ?int $idApprover2 = null;

    public static function getQueue(): Collection
    {
        global $db; // i know
        $query = $db->executeQuery(
            "select * from wormrp_queue where approvalTime is null or postTime > now() - interval 1 month order by approvalTime ASC, postTime ASC"
        );
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

    public static function getSingleItem(int $id): ?self
    {
        global $db; // i know
        $query = $db->executeQuery(
            "select * from wormrp_queue where idPost = ?",
            [$id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        );
        if ($res = $query->fetchAssociative()) {
            return self::createFromDBrow($res);
        } else {
            return null;
        }
    }

    public static function getRedditNameFromDiscord(int $idUser): ?string
    {
        global $db; // i know
        $query = $db->executeQuery(
            "select * from wormrp_users where user = ?",
            [$idUser],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        );
        if ($res = $query->fetchAssociative()) {
            return $res['redditName'];
        }
        return null;
    }

    public function getStateClass(): string
    {
        return match ($this->getState()) {
            self::STATE_PENDING => "pending",
            self::STATE_CLAIMED => "claimed",
            self::STATE_APPROVED => "approved",
        };
    }

    public function getState(): int
    {
        if ($this->approvalTime instanceof Carbon) {
            return self::STATE_APPROVED;
        } elseif ($this->claimTime instanceof Carbon) {
            return self::STATE_CLAIMED;
        } else {
            return self::STATE_PENDING;
        }
    }

    public function claim(int $id): bool
    {
        global $db;

        $q = $db->prepare(
            "update wormrp_queue set claimTime = now(), idApprover1 = ?, approvalTime = null where idPost = ?"
        );
        $q->bindValue(1, $id, \Doctrine\DBAL\ParameterType::INTEGER);
        $q->bindValue(2, $this->idPost, \Doctrine\DBAL\ParameterType::INTEGER);
        return (bool)$q->executeStatement();
    }

    public function complete(): bool
    {
        global $db;

        $q = $db->prepare("update wormrp_queue set approvalTime = now() where idPost = ?");
        $q->bindValue(1, $this->idPost, \Doctrine\DBAL\ParameterType::INTEGER);
        return (bool)$q->executeStatement();
    }

    public function reset(): bool
    {
        global $db;

        $q = $db->prepare(
            "update wormrp_queue set approvalTime = null, claimTime = null, idApprover1 = null, idApprover2 = null where idPost = ?"
        );
        $q->bindValue(1, $this->idPost, \Doctrine\DBAL\ParameterType::INTEGER);
        return (bool)$q->executeStatement();
    }

    public function create(): bool
    {
        global $db;

        $q = $db->prepare(
            "insert into wormrp_queue (idPost, flair, author, url, title, postTime) values (?, ?, ?, ?, ?, ?)"
        );
        $q->bindValue(1, $this->idPost);
        $q->bindValue(2, $this->flair);
        $q->bindValue(3, $this->author);
        $q->bindValue(4, $this->url);
        $q->bindValue(5, $this->title);
        $q->bindValue(6, $this->postTime);
        return (bool)$q->executeStatement();
    }
}