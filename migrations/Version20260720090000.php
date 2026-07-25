<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260720090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Project.descriptionEn, contextEn, objectivesEn, featuresEn';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD description_en LONGTEXT DEFAULT NULL, ADD context_en LONGTEXT DEFAULT NULL, ADD objectives_en LONGTEXT DEFAULT NULL, ADD features_en LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project DROP description_en, DROP context_en, DROP objectives_en, DROP features_en');
    }
}
