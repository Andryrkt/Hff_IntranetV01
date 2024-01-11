<?php
class LdapControl{
    private $LdapModel;

    public function __construct(LdapConnect $LdapModel)
    {
        $this->LdapModel = $LdapModel;
    }
    public function connect_to_user($user,$pswd){
       return $this->LdapModel->userConnect($user,$pswd);
    }
}

 