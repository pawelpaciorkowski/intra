<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240304110103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE pages (id INT AUTO_INCREMENT NOT NULL, is_active TINYINT(1) NOT NULL, title VARCHAR(512) NOT NULL, short LONGTEXT NOT NULL, `long` LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE pages_to_categories (page_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_CE3A4015C4663E4 (page_id), INDEX IDX_CE3A401512469DE2 (category_id), PRIMARY KEY(page_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE pages_to_categories ADD CONSTRAINT FK_CE3A4015C4663E4 FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE pages_to_categories ADD CONSTRAINT FK_CE3A401512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE'
        );
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pages_to_categories DROP FOREIGN KEY FK_CE3A4015C4663E4');
        $this->addSql('ALTER TABLE pages_to_categories DROP FOREIGN KEY FK_CE3A401512469DE2');
        $this->addSql('DROP TABLE pages');
        $this->addSql('DROP TABLE pages_to_categories');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
