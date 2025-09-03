#!/bin/bash

# Script de lancement des tests DOM avec Git Bash

echo "=== Tests DOM - Module de gestion des ordres de mission ==="
echo ""

# Vérifier que PHPUnit est installé
if ! command -v phpunit &> /dev/null; then
    echo "❌ PHPUnit n'est pas installé. Installation..."
    composer require --dev phpunit/phpunit
fi

# Vérifier que les dépendances sont installées
if [ ! -d "../vendor" ]; then
    echo "❌ Les dépendances Composer ne sont pas installées. Installation..."
    cd .. && composer install && cd tests
fi

echo "📁 Répertoire de travail: $(pwd)"
echo ""

# Menu de sélection des tests
echo "Choisissez le type de tests à exécuter:"
echo "1) Tests unitaires uniquement"
echo "2) Tests fonctionnels uniquement"
echo "3) Tests d'intégration uniquement"
echo "4) Tous les tests DOM"
echo "5) Tests avec couverture de code"
echo "6) Tests en mode verbose"
echo ""

read -p "Votre choix (1-6): " choice

case $choice in
    1)
        echo "🧪 Lancement des tests unitaires..."
        phpunit --testsuite "DOM Unit Tests" --colors=always
        ;;
    2)
        echo "🔧 Lancement des tests fonctionnels..."
        phpunit --testsuite "DOM Functional Tests" --colors=always
        ;;
    3)
        echo "🔗 Lancement des tests d'intégration..."
        phpunit --testsuite "DOM Integration Tests" --colors=always
        ;;
    4)
        echo "🎯 Lancement de tous les tests DOM..."
        phpunit --testsuite "DOM All Tests" --colors=always
        ;;
    5)
        echo "📊 Lancement des tests avec couverture de code..."
        phpunit --testsuite "DOM All Tests" --coverage-html coverage --colors=always
        echo "📈 Rapport de couverture généré dans le dossier 'coverage'"
        ;;
    6)
        echo "📝 Lancement des tests en mode verbose..."
        phpunit --testsuite "DOM All Tests" --verbose --colors=always
        ;;
    *)
        echo "❌ Choix invalide. Lancement de tous les tests par défaut..."
        phpunit --testsuite "DOM All Tests" --colors=always
        ;;
esac

echo ""
echo "=== Tests terminés ==="
