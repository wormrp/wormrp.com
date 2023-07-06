<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Controller;

use Nin\Nin;
use WormRP\Model\Character;
use WormRP\Model\Post;

class Thread extends \WormRP\Controller
{
    /**
     * @var \WormRP\Model\User[]
     */
    public array $participants = [];

    public \WormRP\Model\Thread|bool $thread = false;

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

        $allUsers = \WormRP\Model\User::findAllByAttributes(['isBanned' => false]);

        $this->render('thread.view', [
            'thread' => $this->thread,
            'allUsers' => $allUsers,
            'participants' => $this->participants,
        ]);
    }

    public function actionReply()
    {
        if (!Nin::user()) {
            $this->redirect("/login");
            return;
        }

        if (isset($_POST['csrf'])) {
            if ($_POST['csrf'] !== \Nin\Nin::getSession('csrf_token')) {
                $this->displayError('Invalid token.');
                return;
            }

            $reply = new Post();
            $reply->idThread = $this->thread->idThread;
            $reply->idAuthor = Nin::uid();

            if (mb_strlen(trim($_POST['post'] ?? "")) > 0) {
                $reply->post = trim($_POST['post']);
            } else {
                $this->displayError('Post body cannot be empty');
                return;
            }

            if ($_POST['character'] != "") {
                /** @var Character $char */
                $char = Character::findByPk($_POST['character']);
                if (!$char) {
                    $this->displayError('Unknown character');
                    return;
                }
                $reply->idCharacter = $char->idCharacter;
            }

            if (($_POST['parent'] ?? "") != "") {
                /** @var Post $parent */
                $parent = Post::findByPk($_POST['parent']);
                if (!$parent || $parent->idThread != $this->thread->idThread) {
                    $this->displayError('Invalid parent id');
                    return;
                }
                $reply->idParent = $parent->idPost;
            }

            if (($_POST['ping'] ?? "") != "") {
                /** @var \WormRP\Model\User $ping */
                $ping = \WormRP\Model\User::findByPk($_POST['ping']);
                if (!$ping) {
                    $this->displayError('Invalid pinged user id');
                    return;
                }
                $reply->idPing = $ping->idUser;
            }

            if ($reply->save()) {
                $this->redirect("/thread/" . $this->thread->idThread . "#post-" . $reply->idPost);
            } else {
                $this->displayError('Error saving reply. Please seek help.');
            }
        } else {
            $this->displayError('Empty form response.');
        }
    }

    public function actionEditReply(int $idPost)
    {
        if (!Nin::user()) {
            $this->redirect("/login");
            return;
        }

        /** @var Post $post */
        $post = Post::findByPk($idPost);
        if (!$post) {
            $this->displayError('Unknown post');
            return;
        }

        if (Nin::user() != $post->author) {
            $this->displayError('you can only edit your own posts!');
            return;
        }

        if (isset($_POST['csrf'])) {
            if ($_POST['csrf'] !== \Nin\Nin::getSession('csrf_token')) {
                $this->displayError('Invalid token.');
                return;
            }

            if (mb_strlen(trim($_POST['post'] ?? "")) > 0) {
                $post->post = trim($_POST['post']);
            } else {
                $this->displayError('Post body cannot be empty');
                return;
            }

            if ($_POST['character'] != "") {
                /** @var Character $char */
                $char = Character::findByPk($_POST['character']);
                if (!$char) {
                    $this->displayError('Unknown character');
                    return;
                }
                $post->idCharacter = $char->idCharacter;
            } else {
                $post->idCharacter = null;
            }

            if (($_POST['ping'] ?? "") != "") {
                /** @var \WormRP\Model\User $ping */
                $ping = \WormRP\Model\User::findByPk($_POST['ping']);
                if (!$ping) {
                    $this->displayError('Invalid pinged user id');
                    return;
                }
                $post->idPing = $ping->idUser;
            } else {
                $post->idPing = null;
            }

            if ($post->save()) {
                $this->redirect("/thread/" . $this->thread->idThread . "#post-" . $post->idPost);
            } else {
                $this->displayError('Error saving reply. Please seek help.');
            }
        } else {
            $this->displayError('Empty form response.');
        }
    }

    public function actionDeleteReply(int $idPost)
    {
        if (!Nin::user()) {
            $this->redirect("/login");
            return;
        }

        /** @var Post $post */
        $post = Post::findByPk($idPost);
        if (!$post) {
            $this->displayError('Unknown post');
            return;
        }

        if (Nin::user() != $post->author) {
            $this->displayError('you can only delete your own posts!');
            return;
        }

        if (isset($_POST['csrf'])) {
            if ($_POST['csrf'] !== \Nin\Nin::getSession('csrf_token')) {
                $this->displayError('Invalid token.');
                return;
            }

            $post->isDeleted = true;

            if ($post->save()) {
                $this->redirect("/thread/" . $this->thread->idThread . "#post-" . $post->idPost);
            } else {
                $this->displayError('Error deleting reply. Please seek help.');
            }
        } else {
            $this->displayError('Empty form response.');
        }
    }

    protected function getUsersInThread()
    {
        $users = [];
        $users[] = $this->thread->creator;
        foreach ($this->thread->posts as $post) {
            if (!$post->author->isBanned && !in_array($post->author, $users)) {
                $users[] = $post->author;
            }
            if ($post->ping && !$post->ping->isBanned && !in_array($post->ping, $users)) {
                $users[] = $post->ping;
            }
        }

        return $users;
    }
}