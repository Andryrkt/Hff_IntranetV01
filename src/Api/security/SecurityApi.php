<?php

namespace App\Api\security;

use App\Controller\Controller;

class SecurityApi extends Controller
{
    public function checkSession()
    {
        $user = $this->getSessionService()->get('user_id', []);

        if (!$user) {
            $statut = ['status' => 'active'];
        } else {
            $statut = ['status' => 'inactive'];
        }


        header("Content-type:application/json");

        echo json_encode($statut);
    }
}
