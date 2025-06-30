<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210920095624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
        $this->addSql('ALTER TABLE phones ADD employee_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE phones ADD CONSTRAINT FK_E3282EF58C03F15C FOREIGN KEY (employee_id) REFERENCES employees (id)'
        );
        $this->addSql('CREATE INDEX IDX_E3282EF58C03F15C ON phones (employee_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE periods CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`'
        );
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF58C03F15C');
        $this->addSql('DROP INDEX IDX_E3282EF58C03F15C ON phones');
        $this->addSql('ALTER TABLE phones DROP employee_id');
    }
}
