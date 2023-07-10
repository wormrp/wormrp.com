<?php
/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace WormRP\Controller;

use Carbon\Carbon;
use GuzzleHttp\Client;
use WormRP\Controller;

class API extends Controller
{
    public function beforeAction($action)
    {
        header('Content-Type: application/json');
        return $action;
    }

    public function actionDeployWebsite()
    {
        $hmac = nf_param('deployHMAC');
        if (!$hmac) {
            $this->displayError('remote deploy disabled', 503);
            return;
        }

        $testCode = hash_hmac("sha256", file_get_contents('php://input'), $hmac);
        $givenCode = apache_request_headers()['X-Hub-Signature'] ?? "";

        if (hash_equals($testCode, $givenCode)) {
            $resp = `./update`;
            echo json_encode(['command' => $resp]);
        } else {
            $this->displayError('invalid hmac', 403);
        }
    }

    public function displayError($error, $code = 500)
    {
        http_response_code($code);
        echo json_encode(['_error' => $error]);
    }

    public function actionRoles()
    {
        $guzzle = new Client([
            'headers' => [
                'User-Agent' => 'wormrp.com <misfit@misfitmaid.com>',
            ]
        ]);

        $urlCharacters = "https://wiki.wormrp.com/w/api.php?" .
            "action=ask&format=json&api_version=3&query=" .
            "[[Status::Active]]|?Author|?Alignment|?Affiliation|limit=500";

        $reqCharacters = $guzzle->get($urlCharacters);

        $items = json_decode($reqCharacters->getBody()->getContents(), true)['query']['results'];

        $characters = [];
        foreach ($items as $item) {
            $item = current($item);
            $x = new \stdClass();
            $x->name = $item['fulltext'];
            $x->url = $item['fullurl'];

            foreach ($item['printouts'] as $k => $v) {
                $x->$k = [];
                foreach ($v as $vals) {
                    if (is_string($vals)) {
                        $x->$k[] = $vals;
                    } elseif (is_array($vals)) {
                        if (array_key_exists("fulltext", $vals)) {
                            $x->$k[] = $vals['fulltext'];
                        } elseif (array_key_exists("timestamp", $vals)) {
                            $x->$k[] = Carbon::createFromTimestamp($vals['timestamp']);
                        } else {
                            $x->$k[] = $vals->fulltext ?? null;
                        }
                    }
                }
                if (!in_array($k, ['Alignment', 'Affiliation'])) {
                    $x->$k = $x->$k[0] ?? null;
                }
            }
            $characters[$x->name] = $x;
        }


        $urlRoles = "https://wiki.wormrp.com/w/api.php?" .
            "action=ask&format=json&api_version=3&query=" .
            "[[:%2B]][[Discord%20role%20ID::%2B]]|?Discord%20role%20ID|limit=500";

        $reqRoles = $guzzle->get($urlRoles);
        $items = json_decode($reqRoles->getBody()->getContents(), true)['query']['results'];
        $roles = [];
        foreach ($items as $item) {
            $item = current($item);
            $name = $item['fulltext'];
            $id = $item['printouts']["Discord role ID"][0];
            $roles[$name] = $id;
        }


        $players = [];
        foreach ($characters as $character) {
            if (!array_key_exists(mb_strtolower($character->Author), $players)) {
                $players[mb_strtolower($character->Author)] = [];
            }

            foreach ($character->Alignment as $a) {
                if (array_key_exists($a, $roles) && !in_array($roles[$a], $players[mb_strtolower($character->Author)])) {
                    $players[mb_strtolower($character->Author)][] = $roles[$a];
                }
            }

            foreach ($character->Affiliation as $a) {
                if (array_key_exists($a, $roles) && !in_array($roles[$a], $players[mb_strtolower($character->Author)])) {
                    $players[mb_strtolower($character->Author)][] = $roles[$a];
                }
            }
        }

        echo json_encode(['roles' => $roles, 'data' => $players]);
    }
}