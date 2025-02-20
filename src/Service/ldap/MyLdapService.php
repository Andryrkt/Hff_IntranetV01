<?php

namespace App\Service\ldap;

use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Exception\LdapException;

class MyLdapService
{
    private Ldap $ldap;

    public function __construct(Ldap $ldap)
    {
        $this->ldap = $ldap;
    }

    public function authenticate(string $username, string $password, string $domain): bool
    {
        try {
            $this->ldap->bind($username . $domain, $password);
            return true;
        } catch (LdapException $e) {
            return false;
        }
    }

    public function searchUsers(string $baseDn, string $filter): array
    {
        $query = $this->ldap->query($baseDn, $filter);
        $result = $query->execute();
        return $result->toArray();
    }
}

