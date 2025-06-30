<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240611155755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE boxes ADD category_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE boxes ADD CONSTRAINT FK_CDF1B2E912469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)'
        );
        $this->addSql('CREATE INDEX IDX_CDF1B2E912469DE2 ON boxes (category_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE boxes DROP FOREIGN KEY FK_CDF1B2E912469DE2');
        $this->addSql('DROP INDEX IDX_CDF1B2E912469DE2 ON boxes');
        $this->addSql('ALTER TABLE boxes DROP category_id');
    }
}
