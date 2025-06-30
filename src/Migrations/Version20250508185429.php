<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508185429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE iso_files_tags (isofile_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_4E703A4E3AACD9 (isofile_id), INDEX IDX_4E703A4EBAD26311 (tag_id), PRIMARY KEY(isofile_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_files_tags (page_file_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_E394A0D969AB92F9 (page_file_id), INDEX IDX_E394A0D9BAD26311 (tag_id), PRIMARY KEY(page_file_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(1024) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_6FBC94265E237E06 (name), INDEX name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE iso_files_tags ADD CONSTRAINT FK_4E703A4E3AACD9 FOREIGN KEY (isofile_id) REFERENCES iso_files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE iso_files_tags ADD CONSTRAINT FK_4E703A4EBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_files_tags ADD CONSTRAINT FK_E394A0D969AB92F9 FOREIGN KEY (page_file_id) REFERENCES page_files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_files_tags ADD CONSTRAINT FK_E394A0D9BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iso_files_tags DROP FOREIGN KEY FK_4E703A4E3AACD9');
        $this->addSql('ALTER TABLE iso_files_tags DROP FOREIGN KEY FK_4E703A4EBAD26311');
        $this->addSql('ALTER TABLE page_files_tags DROP FOREIGN KEY FK_E394A0D969AB92F9');
        $this->addSql('ALTER TABLE page_files_tags DROP FOREIGN KEY FK_E394A0D9BAD26311');
        $this->addSql('DROP TABLE iso_files_tags');
        $this->addSql('DROP TABLE page_files_tags');
        $this->addSql('DROP TABLE tags');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
