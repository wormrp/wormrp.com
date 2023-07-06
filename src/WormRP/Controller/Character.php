<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Controller;

use Intervention\Image\ImageManager;
use Nin\Nin;

class Character extends \WormRP\Controller
{
    public \WormRP\Model\Character|bool $character = false;

    function __construct($idChar)
    {
        parent::__construct();
        if (is_numeric($idChar)) {
            $this->character = \WormRP\Model\Character::findByPk($idChar);
        }
    }

    public function beforeAction($action)
    {
        if (!$this->character) {
            $this->displayError('Character not found.', 404);
            return false;
        }
        return $action;
    }

    public function actionAvatar()
    {
        if (is_null($this->character->picture)) {
            $this->displayError('Character has no picture.', 404);
            return false;
        }

        $av = base64_decode($this->character->picture);

        header("Content-Type: image/png");
        header('Content-Length: ' . strlen($av));
        header("Digest: sha256-" . base64_encode(hash("sha256", $av, true)));
        echo $av;
    }

    public function actionSetAvatar(int $idChar)
    {
        if (!Nin::user()) {
            $this->redirect("/login");
            return false;
        }

        if ($this->character->idAuthor != Nin::uid()) {
            $this->displayError('can only edit your own characters!', 403);
            return false;
        }

        if (isset($_POST['csrf'])) {
            if ($_POST['csrf'] !== \Nin\Nin::getSession('csrf_token')) {
                $this->displayError('Invalid token.');
                return false;
            }

            if (!array_key_exists('avatar', $_FILES)) {
                $this->displayError('no image uploaded');
                return false;
            }
            $file = $_FILES['avatar'];

            if (!in_array($file['type'], ['image/png', 'image/jpeg'])) {
                $this->displayError('invalid image format');
                return false;
            }

            $manager = new ImageManager();
            $img = $manager->make($file['tmp_name']);
            $img->fit(256, 256, function ($constraint) {
                $constraint->upsize();
            });
            $this->character->picture = base64_encode($img->encode("png"));
            $this->character->save();

            $this->redirect("/characters");
        }
    }

}