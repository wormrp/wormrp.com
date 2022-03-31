<?php

/*
 * Copyright (c) 2022 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Handler;

use Doctrine\DBAL\ParameterType;

class AuthHandler
{

    public function respond(array $vars)
    {
        global $config, $db;

        $provider = new \Wohali\OAuth2\Client\Provider\Discord([
            'clientId' => $config['clientId'],
            'clientSecret' => $config['clientSecret'],
            'redirectUri' => 'http://localhost:8000/auth'
        ]);

        if (!isset($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl(['scope' => ['identify']]) . "&prompt=none";;
            $_SESSION['oauth2state'] = $provider->getState();

            if (array_key_exists('HTTP_REFERER', $_SERVER)) {
                $_SESSION['post_auth_redirect'] = $_SERVER['HTTP_REFERER'];
            }

            header('Location: ' . $authUrl);
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state. Try again, sorry :(');
        } else {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            try {
                $user = $provider->getResourceOwner($token);

                $_SESSION['wormrp.com/auth'] = $token;

                $_SESSION['discordTag'] = $user->getUsername() . "#" . $user->getDiscriminator();
                $_SESSION['discordAv'] = $user->getAvatarHash();
                $_SESSION['discordID'] = $user->getId();
                $_SESSION['isStaff'] = false;

                $query = $db->executeQuery(
                    "select * from wormrp_staff where idUser = ?",
                    [$_SESSION['discordID']],
                    [ParameterType::INTEGER]
                );
                if ($query->rowCount() > 0) {
                    if (in_array($query->fetchAssociative()['staffRole'], [456321111945248779])) { // todo: CCA check
                        $_SESSION['isStaff'] = true;
                    }
                } else {
                    $_SESSION['isStaff'] = false;
                }

                $redirect = $_SESSION['post_auth_redirect'] ?? '/';
                header("Location: $redirect");
            } catch (\Throwable $e) {
                // var_dump($e);
                // Failed to get user details
                exit('Try again, sorry :(');
            }
        }
    }
}
