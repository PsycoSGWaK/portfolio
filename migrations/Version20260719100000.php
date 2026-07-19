<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create profile table (singleton row for the admin-editable profile photo)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, photo_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO profile (id, photo_name) VALUES (1, NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE profile');
    }
}
