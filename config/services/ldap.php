<?php

use Symfony\Component\Ldap\Ldap;

return function (\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) {
    // Enregistrer le service LDAP
    $containerBuilder->register('ldap', Ldap::class)
    ->setFactory([Ldap::class, 'create'])
    ->setArguments([
        'ext_ldap',
        [
            'host' => '192.168.0.1',   // Votre adresse LDAP
            'port' => 389,             // Le port LDAP
            'encryption' => 'none',            // 'tls' si vous utilisez TLS, sinon null
            'options' => [
                'protocol_version' => 3,     // Version du protocole LDAP
                'referrals' => false, // Désactiver les referrals
            ],
        ],
    ])
    ->setPublic(true);
    // Alias pour autowiring
    $containerBuilder->setAlias(Ldap::class, 'ldap')->setPublic(true);

    $containerBuilder->register(\App\Service\ldap\MyLdapService::class, \App\Service\ldap\MyLdapService::class)
    ->setAutowired(true)
    ->setAutoconfigured(true)
    ->setPublic(true);

};
