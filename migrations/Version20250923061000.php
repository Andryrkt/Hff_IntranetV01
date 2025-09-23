<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration d'exemple - Ajout d'une table de test
 */
final class Version20250923061000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout d\'une table de test pour démontrer les migrations';
    }

    public function up(Schema $schema): void
    {
        // Créer une table de test
        $this->addSql('CREATE TABLE test_migration (
            id INT IDENTITY(1,1) PRIMARY KEY,
            nom NVARCHAR(255) NOT NULL,
            description NVARCHAR(500),
            date_creation DATETIME2 DEFAULT GETDATE(),
            actif BIT DEFAULT 1
        )');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la table de test
        $this->addSql('DROP TABLE test_migration');
    }
}
