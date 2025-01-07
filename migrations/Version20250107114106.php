<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250107114106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jobs ADD logo_background VARCHAR(7) NOT NULL, ADD apply VARCHAR(255) NOT NULL, ADD description LONGTEXT DEFAULT NULL, ADD requirements_content LONGTEXT NOT NULL, ADD requirements_items JSON NOT NULL, ADD role_content LONGTEXT NOT NULL, ADD role_items JSON NOT NULL, ADD website VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jobs DROP logo_background, DROP apply, DROP description, DROP requirements_content, DROP requirements_items, DROP role_content, DROP role_items, DROP website');
    }
}
