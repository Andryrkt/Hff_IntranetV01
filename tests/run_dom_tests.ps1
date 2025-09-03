# Script PowerShell pour lancer les tests DOM

Write-Host "=== Tests DOM - Module de gestion des ordres de mission ===" -ForegroundColor Cyan
Write-Host ""

# Vérifier que PHPUnit est installé
if (-not (Get-Command phpunit -ErrorAction SilentlyContinue)) {
    Write-Host "❌ PHPUnit n'est pas installé. Installation..." -ForegroundColor Red
    composer require --dev phpunit/phpunit
}

# Vérifier que les dépendances sont installées
if (-not (Test-Path "../vendor")) {
    Write-Host "❌ Les dépendances Composer ne sont pas installées. Installation..." -ForegroundColor Red
    Set-Location ..
    composer install
    Set-Location tests
}

Write-Host "📁 Répertoire de travail: $(Get-Location)" -ForegroundColor Green
Write-Host ""

# Menu de sélection des tests
Write-Host "Choisissez le type de tests à exécuter:" -ForegroundColor Yellow
Write-Host "1) Tests unitaires uniquement"
Write-Host "2) Tests fonctionnels uniquement"
Write-Host "3) Tests d'intégration uniquement"
Write-Host "4) Tous les tests DOM"
Write-Host "5) Tests avec couverture de code"
Write-Host "6) Tests en mode verbose"
Write-Host ""

$choice = Read-Host "Votre choix (1-6)"

switch ($choice) {
    "1" {
        Write-Host "🧪 Lancement des tests unitaires..." -ForegroundColor Blue
        phpunit --testsuite "DOM Unit Tests" --colors=always
    }
    "2" {
        Write-Host "🔧 Lancement des tests fonctionnels..." -ForegroundColor Blue
        phpunit --testsuite "DOM Functional Tests" --colors=always
    }
    "3" {
        Write-Host "🔗 Lancement des tests d'intégration..." -ForegroundColor Blue
        phpunit --testsuite "DOM Integration Tests" --colors=always
    }
    "4" {
        Write-Host "🎯 Lancement de tous les tests DOM..." -ForegroundColor Blue
        phpunit --testsuite "DOM All Tests" --colors=always
    }
    "5" {
        Write-Host "📊 Lancement des tests avec couverture de code..." -ForegroundColor Blue
        phpunit --testsuite "DOM All Tests" --coverage-html coverage --colors=always
        Write-Host "📈 Rapport de couverture généré dans le dossier 'coverage'" -ForegroundColor Green
    }
    "6" {
        Write-Host "📝 Lancement des tests en mode verbose..." -ForegroundColor Blue
        phpunit --testsuite "DOM All Tests" --verbose --colors=always
    }
    default {
        Write-Host "❌ Choix invalide. Lancement de tous les tests par défaut..." -ForegroundColor Red
        phpunit --testsuite "DOM All Tests" --colors=always
    }
}

Write-Host ""
Write-Host "=== Tests terminés ===" -ForegroundColor Cyan
