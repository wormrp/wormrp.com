<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP;

use Carbon\Carbon;

class Model extends \Nin\Model
{
    public function __get($name)
    {
        if (in_array($name, ['dateCreated', 'dateUpdated'])) {
            return new Carbon(parent::__get($name));
        } else {
            return parent::__get($name);
        }
    }
}