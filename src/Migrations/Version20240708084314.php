<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708084314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668B2414DD3');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668B2414DD3 FOREIGN KEY (category_template_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668B2414DD3');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668B2414DD3 FOREIGN KEY (category_template_id) REFERENCES category_templates (id)');
    }
}
