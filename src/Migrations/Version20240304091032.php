<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240304091032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, icon_id INT DEFAULT NULL, additional_icon_id INT DEFAULT NULL, link_id INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, depth SMALLINT NOT NULL, lft SMALLINT NOT NULL, rgt SMALLINT NOT NULL, name VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_3AF34668727ACA70 (parent_id), INDEX IDX_3AF3466854B9D732 (icon_id), INDEX IDX_3AF34668553304C6 (additional_icon_id), INDEX IDX_3AF34668ADA40271 (link_id), INDEX lft_idx (lft), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE categories_to_links (category_id INT NOT NULL, link_id INT NOT NULL, INDEX IDX_C40391112469DE2 (category_id), INDEX IDX_C403911ADA40271 (link_id), PRIMARY KEY(category_id, link_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE categories ADD CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE categories ADD CONSTRAINT FK_3AF3466854B9D732 FOREIGN KEY (icon_id) REFERENCES icons (id)'
        );
        $this->addSql(
            'ALTER TABLE categories ADD CONSTRAINT FK_3AF34668553304C6 FOREIGN KEY (additional_icon_id) REFERENCES icons (id)'
        );
        $this->addSql(
            'ALTER TABLE categories ADD CONSTRAINT FK_3AF34668ADA40271 FOREIGN KEY (link_id) REFERENCES links (id)'
        );
        $this->addSql(
            'ALTER TABLE categories_to_links ADD CONSTRAINT FK_C40391112469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE categories_to_links ADD CONSTRAINT FK_C403911ADA40271 FOREIGN KEY (link_id) REFERENCES links (id) ON DELETE CASCADE'
        );
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668727ACA70');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF3466854B9D732');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668553304C6');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668ADA40271');
        $this->addSql('ALTER TABLE categories_to_links DROP FOREIGN KEY FK_C40391112469DE2');
        $this->addSql('ALTER TABLE categories_to_links DROP FOREIGN KEY FK_C403911ADA40271');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE categories_to_links');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
