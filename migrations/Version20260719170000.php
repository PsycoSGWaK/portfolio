<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create skill_category and skill tables, seed real content';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE skill_category (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, icon VARCHAR(16) DEFAULT NULL, published TINYINT NOT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, label VARCHAR(255) NOT NULL, position INT NOT NULL, INDEX IDX_5E3DE47712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE47712469DE2 FOREIGN KEY (category_id) REFERENCES skill_category (id)');

        $this->addSql("INSERT INTO skill_category (id, label, icon, published, position) VALUES (1, 'Compétences techniques', '💻', 1, 0)");
        $this->addSql("INSERT INTO skill_category (id, label, icon, published, position) VALUES (2, 'Outils de versioning', '🔀', 1, 1)");
        $this->addSql("INSERT INTO skill_category (id, label, icon, published, position) VALUES (3, 'Méthodologies', '📋', 1, 2)");
        $this->addSql("INSERT INTO skill_category (id, label, icon, published, position) VALUES (4, 'Autres outils', '🛠️', 1, 3)");
        $this->addSql("INSERT INTO skill_category (id, label, icon, published, position) VALUES (5, 'Langues', '🗣️', 1, 4)");
        $this->addSql("INSERT INTO skill_category (id, label, icon, published, position) VALUES (6, 'Soft skills', '🤝', 1, 5)");

        $technical = ['PHP', 'JavaScript', 'HTML/CSS', 'VBA', 'SQL', 'Symfony', 'JSON'];
        foreach ($technical as $i => $label) {
            $this->addSql("INSERT INTO skill (category_id, label, position) VALUES (1, '" . str_replace("'", "''", $label) . "', $i)");
        }

        $versioning = ['Git', 'GitHub', 'GitLab', 'GitKraken'];
        foreach ($versioning as $i => $label) {
            $this->addSql("INSERT INTO skill (category_id, label, position) VALUES (2, '" . str_replace("'", "''", $label) . "', $i)");
        }

        $methodologies = ['Agile (Scrum, Kanban)', 'Cycle en V', 'BPMN'];
        foreach ($methodologies as $i => $label) {
            $this->addSql("INSERT INTO skill (category_id, label, position) VALUES (3, '" . str_replace("'", "''", $label) . "', $i)");
        }

        $otherTools = ['Iterop', 'Figma', 'Photoshop'];
        foreach ($otherTools as $i => $label) {
            $this->addSql("INSERT INTO skill (category_id, label, position) VALUES (4, '" . str_replace("'", "''", $label) . "', $i)");
        }

        $languages = ['Français (courant)', 'Anglais (A2)'];
        foreach ($languages as $i => $label) {
            $this->addSql("INSERT INTO skill (category_id, label, position) VALUES (5, '" . str_replace("'", "''", $label) . "', $i)");
        }

        $softSkills = ['Créativité', "Travail d'équipe", 'Gestion de projet', 'Communication'];
        foreach ($softSkills as $i => $label) {
            $this->addSql("INSERT INTO skill (category_id, label, position) VALUES (6, '" . str_replace("'", "''", $label) . "', $i)");
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE47712469DE2');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE skill_category');
    }
}
