<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP;

use Nin\Nin;
use WormRP\Model\User;

class UserController extends Controller
{
    protected OAuth $oauth;

    public function __construct()
    {
        parent::__construct();
        $this->oauth = new OAuth();
    }

    public function actionLogin()
    {

        if (Nin::uid() !== false) {
            $this->redirect('/');
            return;
        }

        if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            Nin::setSession("postAuthRedirect", $_SERVER['HTTP_REFERER']);
        }

        $this->redirect($this->oauth->getRedirect());
    }

    public function actionAuth($state, $code)
    {
        if ($state != Nin::getSession('oauth2state')) {
            Nin::unsetSession('oauth2state');
            $this->displayError("Invalid OAuth state. Please try again or seek help.", 401);
            return;
        }

        $token = $this->oauth->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        try {
            // object(Wohali\OAuth2\Client\Provider\DiscordResourceOwner)[36]
            //  protected 'response' =>
            //    array (size=14)
            //      'id' => string '297969955356540929' (length=18)
            //      'username' => string 'misfitmaid' (length=10)
            //      'global_name' => string 'MisfitMaid' (length=10)
            //      'avatar' => string 'e0f981cd0358e3adf5c4702dac7cdd75' (length=32)
            //      'discriminator' => string '0' (length=1)
            //      'public_flags' => int 768
            //      'flags' => int 768
            //      'banner' => string 'a_0ef8cbb956f37768e74929dcb1e1f36f' (length=34)
            //      'banner_color' => null
            //      'accent_color' => null
            //      'locale' => string 'en-GB' (length=5)
            //      'mfa_enabled' => boolean true
            //      'premium_type' => int 2
            //      'avatar_decoration' => null

            $resp = $this->oauth->provider->getResourceOwner($token)->toArray();

            $user = User::findByAttributes(array('idUser' => $resp['id']));

            if (!$user) {
                $user = new User();
                $user->idUser = (int)$resp['id'];
                $user->username = $resp['username'];
                $user->displayName = $resp['global_name'];
            }
            $user->avatar = $resp['avatar'];
            $user->banner = $resp['banner'];
            $user->save();


            Nin::setuid($user->idUser);
            Nin::setSession('csrf_token', Nin::randomString(32));

            $redirect = Nin::getSession("postAuthRedirect");
            if ($redirect) {
                $this->redirect($redirect);
            } else {
                $this->redirect("/");
            }
        } catch (\Throwable) {
            $this->displayError("Unknown error. Please try again or seek help.", 500);
            return;
        }
    }
}
