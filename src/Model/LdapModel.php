<?php

namespace App\Model;

class LdapModel
{
    private $ldapHost;
    private $ldapPort;
    private $ldapconn;
    private $Domain;
    private $ldap_dn;

    public function __construct()
    {
        $this->ldapHost = $_ENV['LDAP_HOST'];
        $this->ldapPort = $_ENV['LDAP_PORT'];
        $this->Domain = $_ENV['LDAP_DOMAIN'];
        $this->ldap_dn = $_ENV['LDAP_DN'];

        $this->ldapconn = ldap_connect("ldap://{$this->ldapHost}:{$this->ldapPort}");

        if (!$this->ldapconn) {
            die("Connexion au serveur LDAP échouée.");
        }

        ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
    }

    public function showconnect()
    {
        return $this->ldapconn;
    }

    /**
     * @Andryrkt
     * 
     * récupère le non d'utilisateur et le mot de passe et comparer avec ce qui dans ldap
     *
     * @param string $user
     * @param string $password
     * @return boolean
     */
    public function userConnect(string $user, string $password): bool
    {
        ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = @ldap_bind($this->ldapconn, $user . $this->Domain, $password);
        return $bind;
    }


    public function infoUser($user, $password): array
    {
        ldap_bind($this->ldapconn, $user . $this->Domain, $password);
        // Recherche dans l'annuaire LDAP
        $search_filter = "(objectClass=*)";
        $search_result = ldap_search($this->ldapconn, $this->ldap_dn, $search_filter);

        if (!$search_result) {
            echo "Échec de la recherche LDAP : " . ldap_error($this->ldapconn);
            return [];
        }


        // Récupération des entrées
        $entries = ldap_get_entries($this->ldapconn, $search_result);


        $data = [];
        if ($entries["count"] > 0) {

            for ($i = 0; $i < $entries["count"]; $i++) {

                // if(isset($entries[$i]["samaccountname"][0]) && isset($entries[$i]["description"][0]) && isset($entries[$i]["mail"][0]) && $entries[$i]['useraccountcontrol'][0] = '512' && $entries[$i]['accountexpires'][0] !== '0'){
                //if(isset($entries[$i]["userprincipalname"][0]) && $entries[$i]['useraccountcontrol'][0] == '512' && $entries[$i]['accountexpires'][0] !== '0'){
                if (isset($entries[$i]["userprincipalname"][0])) {

                    $data[$entries[$i]["samaccountname"][0]] = [
                        "nom" => $entries[$i]["sn"][0] ?? '',
                        "prenom" => $entries[$i]["givenname"][0] ?? '',
                        "nomPrenom" => $entries[$i]["name"][0],
                        "fonction" => $entries[$i]["description"][0] ?? '',
                        "numeroTelephone" => $entries[$i]["telephonenumber"][0] ?? '',
                        "nomUtilisateur" => $entries[$i]["samaccountname"][0],
                        "email" => $entries[$i]["mail"][0] ?? '',
                        "nameUserMain" => $entries[$i]["userprincipalname"][0]
                    ];
                }
            }
        } else {
            echo "Aucune entrée trouvée.\n";
        }


        // Fermer la connexion LDAP
        // ldap_unbind($this->ldapconn);

        return $data;
    }
    // public function searchLdapUser()
    // {
    //     // Requête LDAP pour récupérer tous les utilisateurs
    //     $search_base = "OU=HFF Users,DC=fraise,DC=hff,DC=mg"; // Remplacez par la base de recherche appropriée
    //     $search_result = ldap_search($this->ldapconn, $search_base, "(objectClass=person)");
    //     $info = ldap_get_entries($this->ldapconn, $search_result);

    //     // Affichage des utilisateurs
    //     foreach ($info as $user) {
    //         if (isset($user['cn'][0])) {
    //             echo "Nom complet: " . $user['cn'][0] . "<br>";
    //         }
    //         if (isset($user['uid'][0])) {
    //             echo "Identifiant utilisateur: " . $user['uid'][0] . "<br>";
    //         }
    //         if (isset($user['mail'][0])) {
    //             echo "Adresse e-mail: " . $user['mail'][0] . "<br>";
    //         }
    //         echo "<hr>";
    //     }
    // }
}
