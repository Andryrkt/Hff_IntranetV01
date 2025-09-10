<?php

require_once 'vendor/autoload.php';

use App\Model\LdapModel;

echo "🔍 Test de la configuration LDAP...\n\n";

try {
    // Configuration LDAP de test
    $ldapHost = 'ldap.forumsys.com';
    $ldapPort = 389;
    $ldapDomain = '';
    $ldapDn = 'dc=example,dc=com';

    echo "Configuration LDAP :\n";
    echo "- Host: $ldapHost\n";
    echo "- Port: $ldapPort\n";
    echo "- Domain: $ldapDomain\n";
    echo "- DN: $ldapDn\n\n";

    // Créer l'instance LdapModel
    $ldapModel = new LdapModel($ldapHost, $ldapPort, $ldapDomain, $ldapDn);

    echo "✅ LdapModel créé avec succès !\n";

    // Test de connexion (utilisateur de test public)
    $testUser = 'einstein';
    $testPassword = 'password';

    echo "🔐 Test de connexion avec l'utilisateur de test : $testUser\n";

    $result = $ldapModel->userConnect($testUser, $testPassword);

    if ($result) {
        echo "✅ Connexion LDAP réussie !\n";
        echo "✅ L'utilisateur '$testUser' a été authentifié avec succès.\n";
    } else {
        echo "❌ Échec de la connexion LDAP.\n";
        echo "❌ L'utilisateur '$testUser' n'a pas pu être authentifié.\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors du test LDAP :\n";
    echo "Message : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . "\n";
    echo "Ligne : " . $e->getLine() . "\n";
}

echo "\n🏁 Test terminé.\n";
