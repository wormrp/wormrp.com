<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP;

class IndexController extends Controller
{
    public function actionIndex()
    {
        $this->render("home", []);
    }
}