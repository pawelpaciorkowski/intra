<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250122165401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE popups (id INT AUTO_INCREMENT NOT NULL, link_id INT DEFAULT NULL, title VARCHAR(1024) NOT NULL, is_active TINYINT(1) NOT NULL, url VARCHAR(100) DEFAULT NULL, temporary_filename VARCHAR(255) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7EB41FA9ADA40271 (link_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE popups ADD CONSTRAINT FK_7EB41FA9ADA40271 FOREIGN KEY (link_id) REFERENCES links (id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE popups DROP FOREIGN KEY FK_7EB41FA9ADA40271');
        $this->addSql('DROP TABLE popups');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
