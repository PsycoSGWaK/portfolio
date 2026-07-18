<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create experience table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE experience (id INT AUTO_INCREMENT NOT NULL, company VARCHAR(255) NOT NULL, `role` VARCHAR(255) NOT NULL, period VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, published TINYINT(1) NOT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql("INSERT INTO experience (company, `role`, period, location, description, published, position) VALUES ('Safran Aircraft Engines', 'Développeur / Gestion de projet', '2022 - Présent', 'Saint-Quentin-en-Yvelines', 'Développement d''une solution pour accroître la qualité et donner une méthodologie unique pour vérifier les tâches quotidiennes.\nÉlaboration et mise en œuvre d''un plan projet pour professionnaliser l''outil, servant d''exemple pour migrer la plupart des outils vers un environnement commun.\nSupervision des projets, gestion des ressources et des délais, assurant la livraison dans les délais prévus.', 1, 0)");
        $this->addSql("INSERT INTO experience (company, `role`, period, location, description, published, position) VALUES ('CentraleSupélec Alumni', 'Développeur / WebMaster', '2021 - 2022', 'Paris', 'Conception d''une application en JavaScript pour l''analyse de données et l''automatisation du processus, augmentant la productivité de l''équipe.\nAdministration et optimisation des outils de l''association.\nInterface entre le prestataire informatique et l''association, assurant une communication fluide et des mises à jour livrées à temps.', 1, 1)");
        $this->addSql("INSERT INTO experience (company, `role`, period, location, description, published, position) VALUES ('Bel', 'Support de proximité / Développeur', '2019 - 2021', 'Suresnes', 'Support technique quotidien aux utilisateurs du siège social, résolvant en moyenne 10 incidents par semaine.\nConception en Angular 10 d''une application de monitoring pour l''ensemble du service DSI, mettant en évidence bugs et vulnérabilités de chaque application en service.', 1, 2)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE experience');
    }
}
