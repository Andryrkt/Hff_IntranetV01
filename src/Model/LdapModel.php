<?php

namespace App\Model;

class LdapModel
{
    private $ldap;
    private string $ldapHost;
    private string $ldapPort;
    private string $domain;
    private string $baseDn;
    private string $username;
    private string $password;
    private bool   $ldapAuthEnabled;

    public function __construct()
    {
        $this->ldapHost        = $_ENV['LDAP_HOST'];
        $this->ldapPort        = $_ENV['LDAP_PORT'];
        $this->domain          = $_ENV['LDAP_DOMAIN'];
        $this->baseDn          = $_ENV['LDAP_DN'];
        $this->username        = $_ENV['LDAP_USER'];
        $this->password        = $_ENV['LDAP_PASSWORD'];
        $this->ldapAuthEnabled = filter_var($_ENV['LDAP_AUTH_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $this->ldap = ldap_connect("ldap://{$this->ldapHost}:{$this->ldapPort}");
        if (!$this->ldap) die("Connexion au serveur LDAP échouée.");

        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
    }

    /**
     * Authentifie un utilisateur dans l'annuaire LDAP
     *
     * @param string $username
     * @param string $password
     * 
     * @return boolean
     */
    public function authenticate(string $username, string $password): bool
    {
        if (!$this->ldapAuthEnabled) return true;

        return @ldap_bind($this->ldap, "{$username}{$this->domain}", $password);
    }

    public function infoUser(): array
    {
        ldap_bind($this->ldap, "{$this->username}{$this->domain}", $this->password);
        $results = ldap_search($this->ldap, $this->baseDn, "(objectClass=*)");

        if (!$results) {
            echo "Échec de la recherche LDAP : " . ldap_error($this->ldap);
            return [];
        }

        $entries = ldap_get_entries($this->ldap, $results);

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
