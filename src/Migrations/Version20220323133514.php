<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220323133514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE collection_point_extra_dates (id INT AUTO_INCREMENT NOT NULL, collection_point_id INT DEFAULT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, comment VARCHAR(1024) DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_70F503F2D17F50A6 (uuid), INDEX IDX_70F503F231A80092 (collection_point_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE collection_point_extra_dates ADD CONSTRAINT FK_70F503F231A80092 FOREIGN KEY (collection_point_id) REFERENCES collection_points (id)'
        );
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE collection_point_extra_dates');
        $this->addSql(
            'ALTER TABLE periods CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`'
        );
    }
}
