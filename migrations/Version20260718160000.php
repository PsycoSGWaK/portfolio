<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create certificate table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE certificate (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, issuer VARCHAR(255) NOT NULL, issue_date DATE DEFAULT NULL, credential_url VARCHAR(255) DEFAULT NULL, badge_image_name VARCHAR(255) DEFAULT NULL, published TINYINT(1) NOT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql("INSERT INTO certificate (title, issuer, issue_date, credential_url, published, position) VALUES ('Titre du certificat (à modifier)', 'Organisme émetteur', NULL, NULL, 0, 0)");
        $this->addSql("INSERT INTO certificate (title, issuer, issue_date, credential_url, published, position) VALUES ('Titre du certificat (à modifier)', 'Organisme émetteur', NULL, NULL, 0, 1)");
        $this->addSql("INSERT INTO certificate (title, issuer, issue_date, credential_url, published, position) VALUES ('Titre du certificat (à modifier)', 'Organisme émetteur', NULL, NULL, 0, 2)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE certificate');
    }
}
