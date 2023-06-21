<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Model;

class User extends \Nin\Model
{


    public static function tablename()
    {
        return 'users';
    }

    public static function primarykey()
    {
        return 'idUser';
    }
}