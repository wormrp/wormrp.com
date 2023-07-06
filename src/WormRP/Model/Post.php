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
 * @property int $idAuthor
 * @property int $idCharacter
 * @property int $idPing
 * @property bool $isDeleted
 * @property string $post
 * @property Carbon $dateCreated
 * @property Carbon $dateUpdates
 *
 * @property User $author
 * @property User $ping
 * @property self $parent
 * @property Thread $thread
 * @property Character $character
 * @property self[] $replies
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
            'ping' => [BELONGS_TO, "WormRP\Model\User", 'idPing'],
            'parent' => [BELONGS_TO, self::class, 'idParent'],
            'replies' => [HAS_MANY, self::class, 'idParent', ['order' => 'asc', 'orderby' => 'dateCreated']],
            'thread' => [BELONGS_TO, "WormRP\Model\Thread", 'idThread'],
            'character' => [BELONGS_TO, "WormRP\Model\Character", 'idCharacter']
        ];
    }

    public function getMarkdown(): string
    {
        global $mdParser;
        return $mdParser->text($this->post);
    }
}