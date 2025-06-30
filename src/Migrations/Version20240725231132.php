<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240725231132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log_requests ADD link_id INT DEFAULT NULL, ADD request LONGBLOB DEFAULT NULL, CHANGE content content LONGBLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE log_requests ADD CONSTRAINT FK_15D61D8EADA40271 FOREIGN KEY (link_id) REFERENCES links (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_15D61D8EADA40271 ON log_requests (link_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log_requests DROP FOREIGN KEY FK_15D61D8EADA40271');
        $this->addSql('DROP INDEX IDX_15D61D8EADA40271 ON log_requests');
        $this->addSql('ALTER TABLE log_requests DROP link_id, DROP request, CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
