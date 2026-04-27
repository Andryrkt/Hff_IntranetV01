<?php

namespace App\Model;

class LdapModel
{
    private $ldapHost;
    private $ldapPort;
    private $ldapconn;
    private $Domain;
    private $ldap_dn;
    private $user;
    private $password;

    public function __construct()
    {
        $this->ldapHost = $_ENV['LDAP_HOST'];
        $this->ldapPort = $_ENV['LDAP_PORT'];
        $this->Domain = $_ENV['LDAP_DOMAIN'];
        $this->ldap_dn = $_ENV['LDAP_DN'];
        $this->user = $_ENV['LDAP_USER'];
        $this->password = $_ENV['LDAP_PASSWORD'];

        $this->ldapconn = ldap_connect("ldap://{$this->ldapHost}:{$this->ldapPort}");

        ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);

        if (!$this->ldapconn) {
            die("Connexion au serveur LDAP échouée.");
        }
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
        $bind = @ldap_bind($this->ldapconn, $user . $this->Domain, $password);
        return $bind;
    }


    public function infoUser(): array
    {
        ldap_bind($this->ldapconn, $this->user . $this->Domain, $this->password);
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
                if (isset($entries[$i]["userprincipalname"][0])) {

                    $data[$entries[$i]["samaccountname"][0]] = [
                        "nom"             => $entries[$i]["sn"][0] ?? '',
                        "prenom"          => $entries[$i]["givenname"][0] ?? '',
                        "nomPrenom"       => $entries[$i]["name"][0],
                        "fonction"        => $entries[$i]["description"][0] ?? '',
                        "numeroTelephone" => $entries[$i]["telephonenumber"][0] ?? '',
                        "nomUtilisateur"  => $entries[$i]["samaccountname"][0],
                        "email"           => $entries[$i]["mail"][0] ?? '',
                        "nameUserMain"    => $entries[$i]["userprincipalname"][0]
                    ];
                }
            }
        } else {
            echo "Aucune entrée trouvée.\n";
        }

        return $data;
    }
}
