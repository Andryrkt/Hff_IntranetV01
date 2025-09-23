<?php

namespace App\Factory;

use App\Model\LdapModel;
use Psr\Log\LoggerInterface;

class LdapModelFactory
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function create(): LdapModel
    {
        $ldapHost = $_ENV['LDAP_HOST'] ?? '192.168.0.1';
        $ldapPort = (int)($_ENV['LDAP_PORT'] ?? 389);
        $ldapDomain = $_ENV['LDAP_DOMAIN'] ?? '@@fraise.hff.mg';
        $ldapDn = $_ENV['LDAP_DN'] ?? 'OU=HFF Users,DC=fraise,DC=hff,DC=mg';

        return new LdapModel($ldapHost, $ldapPort, $ldapDomain, $ldapDn, $this->logger);
    }
}
