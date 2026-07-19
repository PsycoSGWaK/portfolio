<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Project.context, Project.role, Project.approach';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD context LONGTEXT DEFAULT NULL, ADD role VARCHAR(255) DEFAULT NULL, ADD approach LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project DROP context, DROP role, DROP approach');
    }
}
