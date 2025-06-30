<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250122172434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
        $this->addSql('ALTER TABLE popups DROP FOREIGN KEY FK_7EB41FA9ADA40271');
        $this->addSql('DROP INDEX IDX_7EB41FA9ADA40271 ON popups');
        $this->addSql('ALTER TABLE popups ADD content LONGTEXT DEFAULT NULL, DROP link_id, DROP url, DROP temporary_filename, DROP original_filename');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE popups ADD link_id INT DEFAULT NULL, ADD url VARCHAR(100) DEFAULT NULL, ADD temporary_filename VARCHAR(255) DEFAULT NULL, ADD original_filename VARCHAR(255) DEFAULT NULL, DROP content');
        $this->addSql('ALTER TABLE popups ADD CONSTRAINT FK_7EB41FA9ADA40271 FOREIGN KEY (link_id) REFERENCES links (id)');
        $this->addSql('CREATE INDEX IDX_7EB41FA9ADA40271 ON popups (link_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
