<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Controller;

class Thread extends \WormRP\Controller
{
    private \WormRP\Model\Thread|bool $thread = false;

    function __construct($idThread)
    {
        parent::__construct();
        if (is_numeric($idThread)) {
            $this->thread = \WormRP\Model\Thread::findByPk($idThread);
        }
    }

    public function beforeAction($action)
    {
        if (!$this->thread) {
            $this->displayError('Thread not found.', 404);
            return false;
        }
        return $action;
    }

    public function actionView()
    {
        $this->addBreadcrumb("Threads", "/threads");
        $this->addBreadcrumb("Viewing thread", "/thread/" . $this->thread->idThread);

        $this->render('thread.view', array(
            'thread' => $this->thread,
        ));
    }
}