<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Project.wip';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD wip TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project DROP wip');
    }
}
