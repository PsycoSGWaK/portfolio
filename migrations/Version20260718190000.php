<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add logoName to Experience and Education';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE experience ADD logo_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE education ADD logo_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE experience DROP logo_name');
        $this->addSql('ALTER TABLE education DROP logo_name');
    }
}
