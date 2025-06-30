<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210922111624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_8D93D649A9F87BD');
        $this->addSql(
            'CREATE TABLE log_requests (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, ip VARCHAR(15) DEFAULT NULL, method VARCHAR(128) NOT NULL, url VARCHAR(1024) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_15D61D8EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE log_requests ADD CONSTRAINT FK_15D61D8EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE'
        );
        $this->addSql('DROP TABLE titles');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
        $this->addSql('DROP INDEX IDX_1483A5E9A9F87BD ON users');
        $this->addSql('ALTER TABLE users DROP title_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE titles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_polish_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' '
        );
        $this->addSql('DROP TABLE log_requests');
        $this->addSql(
            'ALTER TABLE periods CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`'
        );
        $this->addSql('ALTER TABLE users ADD title_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE users ADD CONSTRAINT FK_8D93D649A9F87BD FOREIGN KEY (title_id) REFERENCES titles (id)'
        );
        $this->addSql('CREATE INDEX IDX_1483A5E9A9F87BD ON users (title_id)');
    }
}
