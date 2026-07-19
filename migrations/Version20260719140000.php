<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Converts Project.role from free text to a JSON array of checked roles
 * (fixed closed list, see Project::ROLE_CHOICES). Existing free-text values
 * aren't valid JSON and don't map cleanly to the new fixed list, so they're
 * cleared rather than migrated — roles need to be re-picked via the admin.
 */
final class Version20260719140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert Project.role from free text to a JSON array of checked roles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE project SET role = NULL');
        $this->addSql('ALTER TABLE project CHANGE role role JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project CHANGE role role VARCHAR(255) DEFAULT NULL');
    }
}
