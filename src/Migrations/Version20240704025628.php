<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240704025628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF531A80092');
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF51095AD64');
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF58C03F15C');
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF52F2A371E');
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF5A76ED395');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF531A80092 FOREIGN KEY (collection_point_id) REFERENCES collection_points (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF51095AD64 FOREIGN KEY (customer_service_id) REFERENCES customer_services (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF58C03F15C FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF52F2A371E FOREIGN KEY (laboratory_id) REFERENCES laboratories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF5A76ED395');
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF58C03F15C');
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF531A80092');
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF51095AD64');
        $this->addSql('ALTER TABLE phones DROP FOREIGN KEY FK_E3282EF52F2A371E');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF58C03F15C FOREIGN KEY (employee_id) REFERENCES employees (id)');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF531A80092 FOREIGN KEY (collection_point_id) REFERENCES collection_points (id)');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF51095AD64 FOREIGN KEY (customer_service_id) REFERENCES customer_services (id)');
        $this->addSql('ALTER TABLE phones ADD CONSTRAINT FK_E3282EF52F2A371E FOREIGN KEY (laboratory_id) REFERENCES laboratories (id)');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
