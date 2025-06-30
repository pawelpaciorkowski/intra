<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241205013200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE special_templates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(1024) NOT NULL, template LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
        $this->addSql('ALTER TABLE specials ADD special_template_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE specials ADD CONSTRAINT FK_224117F6CE5FDC10 FOREIGN KEY (special_template_id) REFERENCES special_templates (id)');
        $this->addSql('CREATE INDEX IDX_224117F6CE5FDC10 ON specials (special_template_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE specials DROP FOREIGN KEY FK_224117F6CE5FDC10');
        $this->addSql('DROP TABLE special_templates');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_224117F6CE5FDC10 ON specials');
        $this->addSql('ALTER TABLE specials DROP special_template_id');
    }
}
