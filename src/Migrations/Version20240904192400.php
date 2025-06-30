<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240904192400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE iso_file_file_history (id INT AUTO_INCREMENT NOT NULL, isofile_id INT DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, file_type VARCHAR(32) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_A481361D3AACD9 (isofile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE iso_file_file_history ADD CONSTRAINT FK_A481361D3AACD9 FOREIGN KEY (isofile_id) REFERENCES iso_files (id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iso_file_file_history DROP FOREIGN KEY FK_A481361D3AACD9');
        $this->addSql('DROP TABLE iso_file_file_history');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
