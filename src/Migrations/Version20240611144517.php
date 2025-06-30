<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240611144517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE boxes (id INT AUTO_INCREMENT NOT NULL, link_id INT DEFAULT NULL, page_id INT DEFAULT NULL, name VARCHAR(1024) NOT NULL, date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, sort SMALLINT NOT NULL, url VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_CDF1B2E9AA9E377A (date), INDEX IDX_CDF1B2E9ADA40271 (link_id), INDEX IDX_CDF1B2E9C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE boxes ADD CONSTRAINT FK_CDF1B2E9ADA40271 FOREIGN KEY (link_id) REFERENCES links (id)'
        );
        $this->addSql(
            'ALTER TABLE boxes ADD CONSTRAINT FK_CDF1B2E9C4663E4 FOREIGN KEY (page_id) REFERENCES pages (id)'
        );
        $this->addSql('ALTER TABLE categories CHANGE name name VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE boxes DROP FOREIGN KEY FK_CDF1B2E9ADA40271');
        $this->addSql('ALTER TABLE boxes DROP FOREIGN KEY FK_CDF1B2E9C4663E4');
        $this->addSql('DROP TABLE boxes');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE categories CHANGE name name VARCHAR(255) NOT NULL');
    }
}
