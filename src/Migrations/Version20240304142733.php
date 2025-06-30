<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240304142733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE employees_to_departments (employee_id INT NOT NULL, department_id INT NOT NULL, INDEX IDX_400C7298C03F15C (employee_id), INDEX IDX_400C729AE80F5DF (department_id), PRIMARY KEY(employee_id, department_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE employees_to_departments ADD CONSTRAINT FK_400C7298C03F15C FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE employees_to_departments ADD CONSTRAINT FK_400C729AE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE'
        );
        $this->addSql('ALTER TABLE employees DROP FOREIGN KEY FK_BA82C300AE80F5DF');
        $this->addSql('DROP INDEX IDX_BA82C300AE80F5DF ON employees');
        $this->addSql('ALTER TABLE employees DROP department_id');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employees_to_departments DROP FOREIGN KEY FK_400C7298C03F15C');
        $this->addSql('ALTER TABLE employees_to_departments DROP FOREIGN KEY FK_400C729AE80F5DF');
        $this->addSql('DROP TABLE employees_to_departments');
        $this->addSql('ALTER TABLE employees ADD department_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE employees ADD CONSTRAINT FK_BA82C300AE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id)'
        );
        $this->addSql('CREATE INDEX IDX_BA82C300AE80F5DF ON employees (department_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
