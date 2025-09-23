# Guide des Migrations Doctrine

## Configuration

Votre projet est maintenant configuré pour utiliser les migrations Doctrine sans Symfony. La configuration se trouve dans :

- `config/doctrine-migrations.php` - Configuration des migrations
- `config/migrations.php` - Script de bootstrap pour les migrations
- `bin/migrations` - Script de console pour exécuter les migrations
- `migrations/` - Dossier contenant les fichiers de migration

## Commandes principales

### Vérifier le statut des migrations
```bash
php bin/migrations status
```

### Lister toutes les migrations disponibles
```bash
php bin/migrations list
```

### Générer une nouvelle migration vide
```bash
php bin/migrations generate
```

### Exécuter toutes les migrations en attente
```bash
php bin/migrations migrate
```

### Exécuter une migration spécifique
```bash
php bin/migrations execute --up 'App\Migrations\Version20250923061000'
```

### Annuler une migration spécifique
```bash
php bin/migrations execute --down 'App\Migrations\Version20250923061000'
```

### Générer une migration basée sur les différences de schéma
```bash
php bin/migrations diff
```

## Structure d'une migration

```php
<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250923061000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Description de votre migration';
    }

    public function up(Schema $schema): void
    {
        // Code pour appliquer la migration
        $this->addSql('CREATE TABLE ma_table (...)');
    }

    public function down(Schema $schema): void
    {
        // Code pour annuler la migration
        $this->addSql('DROP TABLE ma_table');
    }
}
```

## Bonnes pratiques

1. **Toujours tester vos migrations** avant de les déployer en production
2. **Écrire des méthodes `down()`** pour pouvoir annuler les migrations
3. **Utiliser des transactions** quand c'est possible (activé par défaut)
4. **Nommer clairement** vos migrations dans la méthode `getDescription()`
5. **Sauvegarder la base de données** avant d'exécuter des migrations importantes

## Exemples d'utilisation

### Ajouter une colonne
```php
public function up(Schema $schema): void
{
    $this->addSql('ALTER TABLE users ADD email NVARCHAR(255)');
}

public function down(Schema $schema): void
{
    $this->addSql('ALTER TABLE users DROP COLUMN email');
}
```

### Créer un index
```php
public function up(Schema $schema): void
{
    $this->addSql('CREATE INDEX idx_users_email ON users (email)');
}

public function down(Schema $schema): void
{
    $this->addSql('DROP INDEX idx_users_email ON users');
}
```

### Modifier une colonne
```php
public function up(Schema $schema): void
{
    $this->addSql('ALTER TABLE users ALTER COLUMN name NVARCHAR(500) NOT NULL');
}

public function down(Schema $schema): void
{
    $this->addSql('ALTER TABLE users ALTER COLUMN name NVARCHAR(255) NOT NULL');
}
```

## Intégration avec vos entités

Pour générer automatiquement des migrations basées sur vos entités Doctrine, utilisez :

```bash
php bin/migrations diff
```

Cette commande compare le schéma de votre base de données avec vos entités Doctrine et génère une migration automatiquement.
