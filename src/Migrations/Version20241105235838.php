<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241105235838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carousels (id INT AUTO_INCREMENT NOT NULL, link_id INT DEFAULT NULL, category_id INT DEFAULT NULL, page_id INT DEFAULT NULL, title VARCHAR(1024) NOT NULL, short_text LONGTEXT DEFAULT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, sort SMALLINT NOT NULL, url VARCHAR(100) DEFAULT NULL, temporary_filename VARCHAR(255) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_1B13184CADA40271 (link_id), INDEX IDX_1B13184C12469DE2 (category_id), INDEX IDX_1B13184CC4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carousels ADD CONSTRAINT FK_1B13184CADA40271 FOREIGN KEY (link_id) REFERENCES links (id)');
        $this->addSql('ALTER TABLE carousels ADD CONSTRAINT FK_1B13184C12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE carousels ADD CONSTRAINT FK_1B13184CC4663E4 FOREIGN KEY (page_id) REFERENCES pages (id)');
        $this->addSql('DROP INDEX index_table_object_id_object_class_idx ON index_table');
        $this->addSql('ALTER TABLE index_table CHANGE object_class object_class LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carousels DROP FOREIGN KEY FK_1B13184CADA40271');
        $this->addSql('ALTER TABLE carousels DROP FOREIGN KEY FK_1B13184C12469DE2');
        $this->addSql('ALTER TABLE carousels DROP FOREIGN KEY FK_1B13184CC4663E4');
        $this->addSql('DROP TABLE carousels');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE index_table CHANGE object_class object_class VARCHAR(256) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX index_table_object_id_object_class_idx ON index_table (object_id, object_class)');
    }
}
