<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Controller;

use Nin\ListViews\ModelListView;

class Threads extends \WormRP\Controller
{
    public function beforeAction($action)
    {
        $this->addBreadcrumb("Threads", "/threads");
        return $action;
    }

    public function actionIndex(int $page = 1)
    {
        $this->list($page, \WormRP\Model\Thread::beginQuery()
            ->select()
            ->orderby('dateUpdated', 'DESC')
        );
    }

    protected function list(int $page, $query)
    {
        $threads = new ModelListView($this, $page, $query);
        $threads->perpage = 100;

        $this->render('threads.list', array(
            'threads' => $threads,
        ));
    }

    public function actionSearch(string $search, int $page = 1)
    {
        if (mb_strlen($search) == 0) {
            $this->redirect('/threads');
            return;
        }

        $this->addBreadcrumb("Search results", "/threads/search?search=" . urlencode($search));

        $this->list($page, \WormRP\Model\Thread::beginQuery()
            ->select()
            ->where('title', '%' . $search . '%', 'LIKE')
            ->orderby('dateUpdated', 'DESC')
        );
    }
}