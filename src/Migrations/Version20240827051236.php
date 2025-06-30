<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240827051236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE iso_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, depth SMALLINT NOT NULL, lft SMALLINT NOT NULL, rgt SMALLINT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9CF7ED61727ACA70 (parent_id), INDEX lft_idx (lft), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE iso_categories ADD CONSTRAINT FK_9CF7ED61727ACA70 FOREIGN KEY (parent_id) REFERENCES iso_categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iso_categories DROP FOREIGN KEY FK_9CF7ED61727ACA70');
        $this->addSql('DROP TABLE iso_categories');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
