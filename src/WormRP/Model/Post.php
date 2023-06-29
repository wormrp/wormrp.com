<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Model;

use Carbon\Carbon;

/**
 * @property int $idPost
 * @property int $idThread
 * @property int $idParent
 * @property int $idCharacter
 * @property string $post
 * @property Carbon $dateCreated
 * @property Carbon $dateUpdates
 *
 * @property User $author
 * @property self $parent
 * @property Thread $thread
 * @property Character $character
 */
class Post extends \WormRP\Model
{
    public static function tablename()
    {
        return 'posts';
    }

    public static function primarykey()
    {
        return 'idPost';
    }

    public function relations()
    {
        return [
            'author' => [BELONGS_TO, "WormRP\Model\User", 'idAuthor'],
            'parent' => [BELONGS_TO, self::class, 'idParent'],
            'thread' => [BELONGS_TO, "WormRP\Model\Thread", 'idThread'],
            'character' => [HAS_ONE, "WormRP\Model\Character", 'idCharacter']
        ];
    }
}