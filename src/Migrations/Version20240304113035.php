<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240304113035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categories ADD page_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE categories ADD CONSTRAINT FK_3AF34668C4663E4 FOREIGN KEY (page_id) REFERENCES pages (id)'
        );
        $this->addSql('CREATE INDEX IDX_3AF34668C4663E4 ON categories (page_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668C4663E4');
        $this->addSql('DROP INDEX IDX_3AF34668C4663E4 ON categories');
        $this->addSql('ALTER TABLE categories DROP page_id');
    }
}
