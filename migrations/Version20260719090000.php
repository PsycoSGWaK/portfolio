<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename Project.githubUrl to sourceUrl';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project CHANGE github_url source_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project CHANGE source_url github_url VARCHAR(255) DEFAULT NULL');
    }
}
