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
 * @property User $creator
 * @property string $tag
 */
class Thread extends \WormRP\Model
{
    public const ALLOWED_TAGS = [
        'Event',
        'Patrol',
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
            'posts' => [HAS_MANY, "WormRP\Model\Post", 'idThread', ['order' => 'desc', 'orderby' => 'dateCreated']]
        ];
    }
}