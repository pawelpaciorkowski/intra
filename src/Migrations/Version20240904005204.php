<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240904005204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iso_files ADD current_file_filename VARCHAR(255) DEFAULT NULL, ADD current_file_original_filename VARCHAR(255) DEFAULT NULL, ADD original_file_filename VARCHAR(255) DEFAULT NULL, ADD original_file_original_filename VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iso_files DROP current_file_filename, DROP current_file_original_filename, DROP original_file_filename, DROP original_file_original_filename');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
