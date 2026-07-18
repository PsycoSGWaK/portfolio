<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create education table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE education (id INT AUTO_INCREMENT NOT NULL, school VARCHAR(255) NOT NULL, degree VARCHAR(255) NOT NULL, period VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, published TINYINT(1) NOT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql("INSERT INTO education (school, degree, period, location, description, published, position) VALUES ('IRIS - École Supérieure d''Informatique', 'Mastère 1 & 2 Expert IT, développement et big data', '2022 - 2024', 'Paris 17e', 'Titre RNCP Niveau 7 : RNCP n°34758 - Manager en stratégie et développement de projet digital', 1, 0)");
        $this->addSql("INSERT INTO education (school, degree, period, location, description, published, position) VALUES ('IRIS - École Supérieure d''Informatique', 'Licence Développeur de solutions digitales', '2021 - 2022', 'Paris 17e', 'Titre RNCP Niveau 6 : RNCP n°38607 - Concepteur de solutions digitales', 1, 1)");
        $this->addSql("INSERT INTO education (school, degree, period, location, description, published, position) VALUES ('IRIS - École Supérieure d''Informatique', 'BTS SIO option SLAM - Programmation informatique', '2019 - 2021', 'Paris 17e', 'Titre RNCP Niveau 5 : RNCP n°35340 - TP BTS Services Informatiques aux Organisations - Option B', 1, 2)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE education');
    }
}
