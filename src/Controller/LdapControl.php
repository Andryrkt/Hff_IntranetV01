<?php

namespace App\Controller;

use App\Model\LdapModel;

class LdapControl
{
    private $LdapModel;

    public function __construct(LdapModel $LdapModel)
    {
        $this->LdapModel = $LdapModel;
    }
    public function connect_to_user($user, $pswd)
    {

        return $this->LdapModel->userConnect($user, $pswd);
    }
}
