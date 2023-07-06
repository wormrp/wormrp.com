<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Controller;

use Nin\ListViews\ModelListView;
use Nin\Nin;
use WormRP\DiscordWebhook;

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

    public function actionNew()
    {
        if (!Nin::user()) { // how did you get here
            $this->redirect("/login");
            return;
        }

        $this->addBreadcrumb("Create thread", "/threads/new");

        if (isset($_POST['csrf'])) {
            if ($_POST['csrf'] !== \Nin\Nin::getSession('csrf_token')) {
                $this->displayError('Invalid token.');
                return;
            }
            $title = trim($_POST['title']);
            $tag = trim($_POST['tag']);

            if (mb_strlen($title) == 0 || !in_array($tag, \WormRP\Model\Thread::ALLOWED_TAGS)) {
                $this->displayError("Invalid thread format, please try again", 400);
                return;
            }

            $thread = new \WormRP\Model\Thread();
            $thread->title = $title;
            $thread->tag = $tag;
            $thread->idCreator = Nin::uid();
            $thread->save();

            $disc = new DiscordWebhook(nf_param("webhooks.doots"));
            $disc->msg = sprintf("<@%s> made a new `%s` thread: %s\nhttps://wormrp.com%s", Nin::uid(), $thread->tag, $thread->title, "/thread/" . $thread->idThread);
            $disc->send();

            $this->redirect("/thread/" . $thread->idThread);
        } else {
            $this->render("threads.new", [
                'allowed_tags' => \WormRP\Model\Thread::ALLOWED_TAGS
            ]);
        }
    }
}