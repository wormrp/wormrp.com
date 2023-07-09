<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Model;

use Carbon\Carbon;

/**
 * @property int $idThread
 * @property int $idCreator
 * @property bool $isOpen
 * @property string $title
 * @property Carbon $dateCreated
 * @property Carbon $dateUpdated
 * @property string $tag
 *
 * @property User $creator
 * @property Post[] $posts
 */
class Thread extends \WormRP\Model
{
    public const ALLOWED_TAGS = [
        'Event',
        'Patrol',
        'PHO',
        'Quest',
        'Lore',
        'A-Class',
        'S-Class',
        'Noncanon'
    ];

    public static function tablename()
    {
        return 'threads';
    }

    public static function primarykey()
    {
        return 'idThread';
    }

    public function relations()
    {
        return [
            'creator' => [BELONGS_TO, "WormRP\Model\User", 'idCreator'],
            'posts' => [HAS_MANY, "WormRP\Model\Post", 'idThread', ['order' => 'asc', 'orderby' => 'dateCreated']]
        ];
    }

    public function getPostCount(): int
    {
        return Post::countByAttributes(['isDeleted' => false, 'idThread' => $this->idThread]);
    }

    public function getLastReplyTime(): Carbon|false
    {
        $lastPost = Post::findAllByAttributes(['isDeleted' => false, 'idThread' => $this->idThread], ['order' => 'desc', 'orderby' => 'dateCreated', 'limit' => 1]);
        if (count($lastPost) == 1) {
            return $lastPost[0]->dateCreated;
        }
        return false;
    }
}