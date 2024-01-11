<?php
class LdapConnect
{
    private $ldapHost  = '192.168.0.1';
    private $ldapPort = 389;
    private $ldapconn;
    private $Domain = "@fraise.hff.mg";
    private $ldap_Dn = "OU=HFF Users,DC=fraise,DC=hff,DC=mg";
    private $Users;
    private $Password;
    public function __construct()
    {
        $this->ldapconn = ldap_connect($this->ldapHost, $this->ldapPort);
    }
    public function showconnect()
    {
        return $this->ldapconn;
    }
    public function userConnect($user, $password)
    {
        $this->Users = $user;
        $this->Password = $password;
        ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = @ldap_bind($this->ldapconn, $user . $this->Domain, $password);
       return $bind;
    }
}
