<?php

namespace App\Model;

class LdapModel
{
    private $ldapHost;
    private $ldapPort;
    private $ldapconn;
    private $Domain;
    private $ldap_dn;
    private $ldapconnInitialized = false; // Nouveau drapeau pour la connexion paresseuse

    public function __construct(
        string $ldapHost,
        string $ldapPort,
        string $domain,
        string $ldapDn
    ) {
        $this->ldapHost = $ldapHost;
        $this->ldapPort = $ldapPort;
        $this->Domain = $domain;
        $this->ldap_dn = $ldapDn;
    }

    private function initializeLdapConnection(): void
    {
        if ($this->ldapconnInitialized) {
            return; // Connexion déjà tentée
        }

        $this->ldapconnInitialized = true; // Marquer comme tentée

        $this->ldapconn = @ldap_connect("ldap://{$this->ldapHost}:{$this->ldapPort}");

        if (!$this->ldapconn) {
            error_log("Connexion au serveur LDAP échouée. Host: {$this->ldapHost}, Port: {$this->ldapPort}");
            throw new \Exception("LDAP est requis mais non disponible. Vérifiez la configuration LDAP.");
        }

        if ($this->ldapconn) {
            ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
        }
    }

    public function showconnect()
    {
        $this->initializeLdapConnection();
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
        $this->initializeLdapConnection(); // S'assurer que la connexion est tentée

        if (!$this->ldapconn) {
            error_log("LDAP non disponible - authentification impossible");
            return false;
        }

        ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = @ldap_bind($this->ldapconn, $user . $this->Domain, $password);
        return $bind;
    }


    public function infoUser($user, $password): array
    {
        $this->initializeLdapConnection(); // S'assurer que la connexion est tentée

        if (!$this->ldapconn) {
            error_log("LDAP non disponible - impossible de récupérer les informations utilisateur");
            return [];
        }

        $bind = @ldap_bind($this->ldapconn, $user . $this->Domain, $password);

        if (!$bind) {
            error_log("Erreur de connexion LDAP : " . ldap_error($this->ldapconn));
            return [];
        }
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
}
