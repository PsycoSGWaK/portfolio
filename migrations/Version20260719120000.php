<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename Project.approach to objectives, add Project.techDocName';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project CHANGE approach objectives LONGTEXT DEFAULT NULL, ADD tech_doc_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project CHANGE objectives approach LONGTEXT DEFAULT NULL, DROP tech_doc_name');
    }
}
