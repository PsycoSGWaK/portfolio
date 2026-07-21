<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Profile contact fields and the contact_message table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profile ADD contact_email VARCHAR(255) DEFAULT NULL, ADD linkedin_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, company VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, handled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, INDEX idx_contact_message_ip_created (ip_address, created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_message');
        $this->addSql('ALTER TABLE profile DROP contact_email, DROP linkedin_url');
    }
}
