<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Model;

use Carbon\Carbon;

/**
 * @property int $idCharacter
 * @property int $status
 * @property int $idAuthor
 * @property string $link
 * @property string $name
 * @property Carbon $dateCreated
 * @property Carbon $dateUpdated
 * @property string $picture
 *
 * @property User $author
 */
class Character extends \WormRP\Model
{
    public const CHAR_PENDING = 0;
    public const CHAR_REVIEW = 1;
    public const CHAR_EDITS = 2;
    public const CHAR_APPROVED = 3;
    public const CHAR_REJECTED = 4;

    public static function tablename()
    {
        return 'characters';
    }

    public static function primarykey()
    {
        return 'idCharacter';
    }

    public function relations()
    {
        return [
            'author' => [BELONGS_TO, "WormRP\Model\User", 'idAuthor'],
        ];
    }

    public function getAvatarURL(): string
    {
        if (is_null($this->picture)) {
            return $this->author->getAvatarURL();
        }
        return sprintf("/character/%s/avatar", $this->idCharacter);
    }

    public function getPostCount(): int
    {
        return Post::countByAttributes(['isDeleted' => false, 'idCharacter' => $this->idCharacter]);
    }
}