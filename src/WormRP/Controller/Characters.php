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
use WormRP\Model\Character;

class Characters extends \WormRP\Controller
{
    public function beforeAction($action)
    {
        $this->addBreadcrumb("Characters", "/characters/");
        return $action;
    }

    public function actionIndex(int $page = 1)
    {
        $this->list($page, \WormRP\Model\Character::beginQuery()
            ->select()
            ->where('status', Character::CHAR_APPROVED)
            ->orderby('dateUpdated', 'DESC')
        );
    }

    protected function list(int $page, $query)
    {
        if (Nin::uid()) {
            $mine = new ModelListView($this, 1, $this->myCharacters());
            $query->where('idAuthor', Nin::uid(), '!=');
        }
        $chars = new ModelListView($this, $page, $query);
        $chars->perpage = 100;


        $this->render('characters.list', array(
            'characters' => $chars,
            'mine' => $mine ?? []
        ));
    }

    protected function myCharacters()
    {
        if (!Nin::user()) {
            $this->redirect("/login");
            return false;
        }
        return \WormRP\Model\Character::beginQuery()
            ->select()
            ->where('idAuthor', Nin::uid())
            ->orderby('dateUpdated', 'DESC');
    }

    public function actionApprovals(int $page = 1)
    {
        $this->addBreadcrumb("Approval Queue", "/characters/queue");

        if (isset($_POST['csrf'])) {
            if (!Nin::user()) {
                $this->redirect("/login");
                return false;
            }
            if (!Nin::user()->isApprover) {
                $this->displayError("not an approver >:(", 403);
                return false;
            }

            if ($_POST['csrf'] !== \Nin\Nin::getSession('csrf_token')) {
                $this->displayError('Invalid token.');
                return false;
            }
            /** @var Character $char */
            $char = Character::findByPk($_POST['idCharacter']);
            $char->status = intval($_POST['status']);
            $char->save();
        }

        $pending = Character::findAllByAttributes([
            'status' => [
                Character::CHAR_PENDING,
                Character::CHAR_REVIEW,
                Character::CHAR_EDITS
            ]
        ]);

        $this->render("characters.approval", [
            'characters' => $pending
        ]);
    }

    public function actionNew()
    {
        if (!Nin::user()) {
            $this->redirect("/login");
            return false;
        }

        $this->addBreadcrumb("Submit character", "/characters/new");

        if (isset($_POST['csrf'])) {
            if ($_POST['csrf'] !== \Nin\Nin::getSession('csrf_token')) {
                $this->displayError('Invalid token.');
                return false;
            }
            $name = trim($_POST['name']);
            $link = trim($_POST['link']);

            if (mb_strlen($name) == 0 || mb_strlen($link) == 0) {
                $this->displayError("Name and link are both required, please try again", 400);
                return false;
            }

            $char = new \WormRP\Model\Character();
            $char->name = $name;
            $char->link = $link;
            $char->status = Character::CHAR_PENDING;
            $char->idAuthor = Nin::uid();
            $char->save();

            $disc = new DiscordWebhook(nf_param("webhooks.queue"));
            $disc->msg = sprintf("<@%s> submitted: %s", Nin::uid(), $char->name);
            $disc->send();

            $this->redirect("/characters");
        } else {
            $this->render("characters.new", []);
        }
    }
}