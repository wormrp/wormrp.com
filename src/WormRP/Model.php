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

    /**
     * hacky shitshow to get twig to play nice with nin (__isset() didnt work)
     */
    public function __call(string $name, array $arguments)
    {
        return $this->__get($name);
    }

    public function __get($name)
    {
        if (in_array($name, ['dateCreated', 'dateUpdated'])) {
            return new Carbon(parent::__get($name));
        } else {
            return parent::__get($name);
        }
    }
}