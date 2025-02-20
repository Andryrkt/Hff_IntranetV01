<?php

namespace App\Service\ldap;

use Symfony\Component\Ldap\Ldap;
use Psr\Log\LoggerInterface;
use Symfony\Component\Ldap\Exception\InvalidCredentialsException;

class MyLdapService
{
    private Ldap $ldap;
    private string $domainSuffix;
    private LoggerInterface $logger;

    public function __construct(
        Ldap $ldap,
        LoggerInterface $logger,
        string $domainSuffix = '@fraise.hff.mg'
    ) {
        $this->ldap = $ldap;
        $this->logger = $logger;
        $this->domainSuffix = $domainSuffix;
    }

    public function authenticate(string $username, string $password): bool
    {
        $usernameWithDomain = $username . $this->domainSuffix;

        try {
            $this->ldap->bind($usernameWithDomain, $password);
            $this->logger->info("Authentification réussie pour l'utilisateur : {$usernameWithDomain}");

            return true;
        } catch (InvalidCredentialsException $e) {
            $this->logger->warning("Échec d'authentification pour l'utilisateur : {$usernameWithDomain}");
            return false;
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'authentification LDAP pour l'utilisateur : {$usernameWithDomain}. Erreur : " . $e->getMessage());
            return false;
        }
    }

    public function searchUsers(string $baseDn, string $filter): array
    {
        try {
            $query = $this->ldap->query($baseDn, $filter);
            $result = $query->execute();
            $entries = $result->toArray();

            $this->logger->info("Recherche LDAP effectuée avec succès sur '{$baseDn}' avec le filtre '{$filter}', Nombre de résultats : " . count($entries));

            return $entries;
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la recherche LDAP sur '{$baseDn}' avec le filtre '{$filter}' : " . $e->getMessage());

            return [];
        }
    }
}


