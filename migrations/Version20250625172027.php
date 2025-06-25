<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625172027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert posted_at from DATETIME to INTEGER (Unix timestamp) via colonne temporaire';
    }

    public function up(Schema $schema): void
    {
        // 1. Ajouter une colonne temporaire
        $this->addSql('ALTER TABLE jobs ADD posted_at_tmp INT DEFAULT NULL');

        // 2. Copier les valeurs converties en timestamp
        $this->addSql('UPDATE jobs SET posted_at_tmp = UNIX_TIMESTAMP(posted_at)');

        // 3. Supprimer l'ancienne colonne
        $this->addSql('ALTER TABLE jobs DROP COLUMN posted_at');

        // 4. Renommer la colonne temporaire
        $this->addSql('ALTER TABLE jobs CHANGE posted_at_tmp posted_at INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // 1. Ajouter une colonne temporaire
        $this->addSql('ALTER TABLE jobs ADD posted_at_tmp DATETIME DEFAULT NULL');

        // 2. Copier les valeurs converties en DATETIME
        $this->addSql('UPDATE jobs SET posted_at_tmp = FROM_UNIXTIME(posted_at)');

        // 3. Supprimer l'ancienne colonne
        $this->addSql('ALTER TABLE jobs DROP COLUMN posted_at');

        // 4. Renommer la colonne temporaire
        $this->addSql('ALTER TABLE jobs CHANGE posted_at_tmp posted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
