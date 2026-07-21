<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add English fields on Profile, Experience, Education and SkillCategory';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profile ADD about_text_en LONGTEXT DEFAULT NULL, ADD highlights_en LONGTEXT DEFAULT NULL, ADD stats_en LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE experience ADD role_en VARCHAR(255) DEFAULT NULL, ADD description_en LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE education ADD degree_en VARCHAR(255) DEFAULT NULL, ADD description_en LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE skill_category ADD label_en VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profile DROP about_text_en, DROP highlights_en, DROP stats_en');
        $this->addSql('ALTER TABLE experience DROP role_en, DROP description_en');
        $this->addSql('ALTER TABLE education DROP degree_en, DROP description_en');
        $this->addSql('ALTER TABLE skill_category DROP label_en');
    }
}
