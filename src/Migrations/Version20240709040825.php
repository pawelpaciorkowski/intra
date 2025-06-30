<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240709040825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE page_templates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(1024) NOT NULL, template LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pages ADD page_template_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E575126651CA FOREIGN KEY (page_template_id) REFERENCES page_templates (id)');
        $this->addSql('CREATE INDEX IDX_2074E575126651CA ON pages (page_template_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575126651CA');
        $this->addSql('DROP TABLE page_templates');
        $this->addSql('DROP INDEX IDX_2074E575126651CA ON pages');
        $this->addSql('ALTER TABLE pages DROP page_template_id');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
