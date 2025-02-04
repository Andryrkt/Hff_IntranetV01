<?php

namespace App\Service\ldap;

use Symfony\Component\Ldap\Ldap;

class MyLdapService
{
    private Ldap $ldap;

    public function __construct(Ldap $ldap)
    {
        $this->ldap = $ldap;
    }

    public function searchUsers(string $baseDn, string $filter): array
    {
        // Effectue une recherche LDAP
        $query = $this->ldap->query($baseDn, $filter);
        $result = $query->execute();
        return $result->toArray();
    }
}
