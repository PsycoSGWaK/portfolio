<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Profile.aboutText, Profile.highlights, Profile.stats';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profile ADD about_text LONGTEXT DEFAULT NULL, ADD highlights LONGTEXT DEFAULT NULL, ADD stats LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profile DROP about_text, DROP highlights, DROP stats');
    }
}
