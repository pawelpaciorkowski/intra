<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240904193230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iso_file_file_history ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE iso_file_file_history ADD CONSTRAINT FK_A481361DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_A481361DA76ED395 ON iso_file_file_history (user_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iso_file_file_history DROP FOREIGN KEY FK_A481361DA76ED395');
        $this->addSql('DROP INDEX IDX_A481361DA76ED395 ON iso_file_file_history');
        $this->addSql('ALTER TABLE iso_file_file_history DROP user_id');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
