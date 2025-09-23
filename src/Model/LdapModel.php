<?php

namespace App\Model;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

class LdapModel
{
    private $ldapHost;
    private $ldapPort;
    private $ldapconn = null;
    private $Domain;
    private $ldap_dn;
    private $ldapconnInitialized = false;
    private $logger;
    private $isConnected = false;

    /**
     * Constructeur de la classe LdapModel
     * @param string $ldapHost Adresse du serveur LDAP
     * @param string|int $ldapPort Port du serveur LDAP
     * @param string $domain Domaine LDAP
     * @param string $ldapDn DN de base pour les recherches LDAP
     * @param PsrLoggerInterface $logger Logger pour les logs
     * @throws \InvalidArgumentException Si les paramètres sont invalides
     */
    public function __construct(
        string $ldapHost,
        $ldapPort,
        string $domain,
        string $ldapDn,
        PsrLoggerInterface $logger
    ) {
        $this->ldapHost = $ldapHost;
        $this->ldapPort = (string)$ldapPort; // Convertir en chaîne pour la validation
        $this->Domain = $domain;
        $this->ldap_dn = $ldapDn;
        $this->logger = $logger;

        $this->validateConnectionParameters();
    }

    /**
     * Valide les paramètres de connexion LDAP
     * @throws \InvalidArgumentException
     */
    private function validateConnectionParameters(): void
    {
        if (empty($this->ldapHost)) {
            throw new \InvalidArgumentException("L'adresse du serveur LDAP ne peut pas être vide.");
        }

        if (empty($this->ldapPort) || !is_numeric($this->ldapPort)) {
            throw new \InvalidArgumentException("Le port LDAP doit être un nombre valide.");
        }

        if (empty($this->Domain)) {
            throw new \InvalidArgumentException("Le domaine LDAP ne peut pas être vide.");
        }

        if (empty($this->ldap_dn)) {
            throw new \InvalidArgumentException("Le DN de base LDAP ne peut pas être vide.");
        }
    }

    /**
     * Initialise la connexion LDAP
     * @throws \RuntimeException
     */
    private function initializeLdapConnection(): void
    {
        // Si déjà connecté et la connexion est valide, ne pas réinitialiser
        if ($this->isConnected && $this->ldapconn) {
            return;
        }

        // Fermer l'ancienne connexion si elle existe
        if ($this->ldapconn) {
            @ldap_unbind($this->ldapconn);
        }

        try {
            $this->logger->info("Tentative de connexion au serveur LDAP", [
                'host' => $this->ldapHost,
                'port' => $this->ldapPort
            ]);

            $this->ldapconn = @ldap_connect("ldap://{$this->ldapHost}:{$this->ldapPort}");

            if (!$this->ldapconn) {
                $this->logger->error("Échec de la connexion au serveur LDAP", [
                    'host' => $this->ldapHost,
                    'port' => $this->ldapPort
                ]);
                throw new \RuntimeException("LDAP est requis mais non disponible. Vérifiez la configuration LDAP.");
            }

            // Configuration des options LDAP
            ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($this->ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 10);
            ldap_set_option($this->ldapconn, LDAP_OPT_TIMELIMIT, 10);

            $this->isConnected = true;
            $this->ldapconnInitialized = true;
            $this->logger->info("Connexion LDAP établie avec succès");
        } catch (\Exception $e) {
            $this->isConnected = false;
            $this->ldapconnInitialized = false;
            $this->logger->error("Erreur lors de l'initialisation LDAP", [
                'message' => $e->getMessage(),
                'host' => $this->ldapHost,
                'port' => $this->ldapPort
            ]);
            throw new \RuntimeException("Impossible d'initialiser la connexion LDAP: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Vérifie si la connexion LDAP est active
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->isConnected && $this->ldapconn !== null;
    }

    /**
     * Teste la santé de la connexion LDAP
     * @return bool
     */
    public function ping(): bool
    {
        try {
            $this->initializeLdapConnection();
            return $this->ldapconn !== false && $this->ldapconn !== null;
        } catch (\Exception $e) {
            $this->logger->warning("Échec du ping LDAP", ['error' => $e->getMessage()]);
            $this->isConnected = false;
            $this->ldapconnInitialized = false;
            return false;
        }
    }

    /**
     * Obtient la connexion LDAP
     * @return resource|false La connexion LDAP
     * @throws \RuntimeException
     */
    public function showconnect()
    {
        $this->initializeLdapConnection();
        return $this->ldapconn;
    }

    /**
     * Authentifie un utilisateur via LDAP
     * @param string $user Nom d'utilisateur
     * @param string $password Mot de passe
     * @return bool True si l'authentification réussit, false sinon
     */
    public function userConnect(string $user, string $password): bool
    {
        try {
            $this->initializeLdapConnection();

            if (!$this->ldapconn) {
                $this->logger->error("LDAP non disponible - authentification impossible");
                return false;
            }

            // Validation des paramètres d'entrée
            if (empty($user) || empty($password)) {
                $this->logger->warning("Tentative d'authentification avec des paramètres vides");
                return false;
            }

            $this->logger->debug("Tentative d'authentification LDAP", ['user' => $user]);

            // Configuration des options LDAP pour l'authentification
            ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($this->ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 10);
            ldap_set_option($this->ldapconn, LDAP_OPT_TIMELIMIT, 10);

            // Tentative de bind avec gestion d'erreur améliorée
            $ldapUser = $user . $this->Domain;
            $this->logger->info("Tentative d'authentification LDAP", [
                'user' => $user,
                'domain' => $this->Domain,
                'ldap_user' => $ldapUser
            ]);

            $bind = @ldap_bind($this->ldapconn, $ldapUser, $password);

            if (!$bind) {
                $ldapError = ldap_error($this->ldapconn);
                $this->logger->error("Échec de l'authentification LDAP", [
                    'user' => $user,
                    'ldap_user' => $ldapUser,
                    'error' => $ldapError
                ]);
            }
            if ($bind) {
                $this->logger->info("Authentification LDAP réussie", ['user' => $user]);
                return true;
            } else {
                $ldapError = ldap_error($this->ldapconn);
                $this->logger->warning("Échec de l'authentification LDAP", [
                    'user' => $user,
                    'error' => $ldapError
                ]);

                // Si l'erreur indique un problème de connexion, marquer comme déconnecté
                if (strpos($ldapError, "Can't contact LDAP server") !== false) {
                    $this->isConnected = false;
                    $this->ldapconnInitialized = false;
                }

                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Exception lors de l'authentification LDAP", [
                'user' => $user,
                'message' => $e->getMessage()
            ]);
            // En cas d'exception, marquer comme déconnecté
            $this->isConnected = false;
            $this->ldapconnInitialized = false;
            return false;
        }
    }


    /**
     * Récupère les informations des utilisateurs depuis LDAP
     * @param string $user Nom d'utilisateur pour l'authentification
     * @param string $password Mot de passe pour l'authentification
     * @return array Tableau des informations utilisateurs
     */
    public function infoUser(string $user, string $password): array
    {
        try {
            $this->initializeLdapConnection();

            if (!$this->ldapconn) {
                $this->logger->error("LDAP non disponible - impossible de récupérer les informations utilisateur");
                return [];
            }

            // Validation des paramètres d'entrée
            if (empty($user) || empty($password)) {
                $this->logger->warning("Tentative de récupération d'informations avec des paramètres vides");
                return [];
            }

            $this->logger->debug("Tentative de récupération des informations utilisateur LDAP", ['user' => $user]);

            $bind = @ldap_bind($this->ldapconn, $user . $this->Domain, $password);

            if (!$bind) {
                $this->logger->error("Erreur de connexion LDAP pour récupération des informations", [
                    'user' => $user,
                    'error' => ldap_error($this->ldapconn)
                ]);
                return [];
            }

            // Recherche dans l'annuaire LDAP
            $search_filter = "(objectClass=*)";
            $search_result = ldap_search($this->ldapconn, $this->ldap_dn, $search_filter);

            if (!$search_result) {
                $this->logger->error("Échec de la recherche LDAP", [
                    'error' => ldap_error($this->ldapconn),
                    'dn' => $this->ldap_dn
                ]);
                return [];
            }

            // Récupération des entrées
            $entries = ldap_get_entries($this->ldapconn, $search_result);

            $data = [];
            if ($entries["count"] > 0) {
                $this->logger->debug("Récupération des entrées LDAP", ['count' => $entries["count"]]);

                for ($i = 0; $i < $entries["count"]; $i++) {
                    if (isset($entries[$i]["userprincipalname"][0])) {
                        $samAccountName = $entries[$i]["samaccountname"][0] ?? '';

                        $data[$samAccountName] = [
                            "nom" => $entries[$i]["sn"][0] ?? '',
                            "prenom" => $entries[$i]["givenname"][0] ?? '',
                            "nomPrenom" => $entries[$i]["name"][0] ?? '',
                            "fonction" => $entries[$i]["description"][0] ?? '',
                            "numeroTelephone" => $entries[$i]["telephonenumber"][0] ?? '',
                            "nomUtilisateur" => $samAccountName,
                            "email" => $entries[$i]["mail"][0] ?? '',
                            "nameUserMain" => $entries[$i]["userprincipalname"][0] ?? ''
                        ];
                    }
                }
            } else {
                $this->logger->info("Aucune entrée trouvée dans LDAP");
            }

            $this->logger->info("Récupération des informations LDAP terminée", ['users_found' => count($data)]);
            return $data;
        } catch (\Exception $e) {
            $this->logger->error("Exception lors de la récupération des informations LDAP", [
                'user' => $user,
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Recherche un utilisateur spécifique dans LDAP
     * @param string $username Nom d'utilisateur à rechercher
     * @param string $user Nom d'utilisateur pour l'authentification
     * @param string $password Mot de passe pour l'authentification
     * @return array|null Informations de l'utilisateur ou null si non trouvé
     */
    public function findUser(string $username, string $user, string $password): ?array
    {
        try {
            $this->initializeLdapConnection();

            if (!$this->ldapconn) {
                $this->logger->error("LDAP non disponible - impossible de rechercher l'utilisateur");
                return null;
            }

            $bind = @ldap_bind($this->ldapconn, $user . $this->Domain, $password);

            if (!$bind) {
                $this->logger->error("Erreur de connexion LDAP pour recherche d'utilisateur", [
                    'user' => $user,
                    'error' => ldap_error($this->ldapconn)
                ]);
                return null;
            }

            $search_filter = "(samaccountname={$username})";
            $search_result = ldap_search($this->ldapconn, $this->ldap_dn, $search_filter);

            if (!$search_result) {
                $this->logger->warning("Échec de la recherche d'utilisateur LDAP", [
                    'username' => $username,
                    'error' => ldap_error($this->ldapconn)
                ]);
                return null;
            }

            $entries = ldap_get_entries($this->ldapconn, $search_result);

            if ($entries["count"] > 0 && isset($entries[0]["userprincipalname"][0])) {
                $entry = $entries[0];
                return [
                    "nom" => $entry["sn"][0] ?? '',
                    "prenom" => $entry["givenname"][0] ?? '',
                    "nomPrenom" => $entry["name"][0] ?? '',
                    "fonction" => $entry["description"][0] ?? '',
                    "numeroTelephone" => $entry["telephonenumber"][0] ?? '',
                    "nomUtilisateur" => $entry["samaccountname"][0] ?? '',
                    "email" => $entry["mail"][0] ?? '',
                    "nameUserMain" => $entry["userprincipalname"][0] ?? ''
                ];
            }

            $this->logger->info("Utilisateur non trouvé dans LDAP", ['username' => $username]);
            return null;
        } catch (\Exception $e) {
            $this->logger->error("Exception lors de la recherche d'utilisateur LDAP", [
                'username' => $username,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Ferme la connexion LDAP
     */
    public function close(): void
    {
        try {
            if ($this->ldapconn && $this->isConnected) {
                @ldap_unbind($this->ldapconn);
                $this->isConnected = false;
                $this->ldapconnInitialized = false;
                $this->ldapconn = null;
                $this->logger->info("Connexion LDAP fermée");
            } else {
                $this->logger->warning("Tentative de fermer une connexion LDAP non établie");
            }
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la fermeture de la connexion LDAP", ['message' => $e->getMessage()]);
        } finally {
            // S'assurer que les états sont réinitialisés même en cas d'erreur
            $this->isConnected = false;
            $this->ldapconnInitialized = false;
            $this->ldapconn = null;
        }
    }

    /**
     * Destructeur - ferme automatiquement la connexion
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Obtient des informations sur la connexion LDAP
     * @return array
     */
    public function getConnectionInfo(): array
    {
        return [
            'host' => $this->ldapHost,
            'port' => $this->ldapPort,
            'domain' => $this->Domain,
            'dn' => $this->ldap_dn,
            'isConnected' => $this->isConnected
        ];
    }
}
