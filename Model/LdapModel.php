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
    public function searchLdapUser()
    {
        // Requête LDAP pour récupérer tous les utilisateurs
        $search_base = "OU=HFF Users,DC=fraise,DC=hff,DC=mg"; // Remplacez par la base de recherche appropriée
        $search_result = ldap_search($this->ldapconn, $search_base, "(objectClass=person)");
        $info = ldap_get_entries($this->ldapconn, $search_result);

        // Affichage des utilisateurs
        foreach ($info as $user) {
            if (isset($user['cn'][0])) {
                echo "Nom complet: " . $user['cn'][0] . "<br>";
            }
            if (isset($user['uid'][0])) {
                echo "Identifiant utilisateur: " . $user['uid'][0] . "<br>";
            }
            if (isset($user['mail'][0])) {
                echo "Adresse e-mail: " . $user['mail'][0] . "<br>";
            }
            echo "<hr>";
        }
    }
}
