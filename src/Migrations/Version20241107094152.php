<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241107094152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE page_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, page_id INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, depth SMALLINT NOT NULL, lft SMALLINT NOT NULL, rgt SMALLINT NOT NULL, name VARCHAR(256) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_311377F6727ACA70 (parent_id), INDEX IDX_311377F6C4663E4 (page_id), INDEX lft_idx (lft), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_file_file_history (id INT AUTO_INCREMENT NOT NULL, page_file_id INT DEFAULT NULL, user_id INT DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, temporary_filename VARCHAR(255) DEFAULT NULL, file_type VARCHAR(32) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5046EB4D69AB92F9 (page_file_id), INDEX IDX_5046EB4DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_files (id INT AUTO_INCREMENT NOT NULL, page_category_id INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, name VARCHAR(256) NOT NULL, description LONGTEXT DEFAULT NULL, sort SMALLINT NOT NULL, current_file_filename VARCHAR(255) DEFAULT NULL, current_file_original_filename VARCHAR(255) DEFAULT NULL, original_file_filename VARCHAR(255) DEFAULT NULL, original_file_original_filename VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_60B4BF8F5FAC390 (page_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page_categories ADD CONSTRAINT FK_311377F6727ACA70 FOREIGN KEY (parent_id) REFERENCES page_categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_categories ADD CONSTRAINT FK_311377F6C4663E4 FOREIGN KEY (page_id) REFERENCES pages (id)');
        $this->addSql('ALTER TABLE page_file_file_history ADD CONSTRAINT FK_5046EB4D69AB92F9 FOREIGN KEY (page_file_id) REFERENCES page_files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_file_file_history ADD CONSTRAINT FK_5046EB4DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE page_files ADD CONSTRAINT FK_60B4BF8F5FAC390 FOREIGN KEY (page_category_id) REFERENCES page_categories (id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_categories DROP FOREIGN KEY FK_311377F6727ACA70');
        $this->addSql('ALTER TABLE page_categories DROP FOREIGN KEY FK_311377F6C4663E4');
        $this->addSql('ALTER TABLE page_file_file_history DROP FOREIGN KEY FK_5046EB4D69AB92F9');
        $this->addSql('ALTER TABLE page_file_file_history DROP FOREIGN KEY FK_5046EB4DA76ED395');
        $this->addSql('ALTER TABLE page_files DROP FOREIGN KEY FK_60B4BF8F5FAC390');
        $this->addSql('DROP TABLE page_categories');
        $this->addSql('DROP TABLE page_file_file_history');
        $this->addSql('DROP TABLE page_files');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
