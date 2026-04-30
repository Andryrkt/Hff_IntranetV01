<?php

namespace App\Api\security;

use App\Controller\Controller;

class SecurityApi extends Controller
{
    public function checkSession()
    {
        $userInfo = $this->getSessionService()->get('user_info');

        header("Content-type:application/json");

        echo json_encode(['status' => $userInfo ? 'active' : 'inactive']);
    }
}
