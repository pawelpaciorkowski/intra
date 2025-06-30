<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240311103458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE files DROP FOREIGN KEY FK_6354059C4663E4');
        $this->addSql('DROP INDEX IDX_6354059C4663E4 ON files');
        $this->addSql('ALTER TABLE files CHANGE page_id file_section_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE files ADD CONSTRAINT FK_635405961CB38CC FOREIGN KEY (file_section_id) REFERENCES file_sections (id)'
        );
        $this->addSql('CREATE INDEX IDX_635405961CB38CC ON files (file_section_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE files DROP FOREIGN KEY FK_635405961CB38CC');
        $this->addSql('DROP INDEX IDX_635405961CB38CC ON files');
        $this->addSql('ALTER TABLE files CHANGE file_section_id page_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE files ADD CONSTRAINT FK_6354059C4663E4 FOREIGN KEY (page_id) REFERENCES file_sections (id)'
        );
        $this->addSql('CREATE INDEX IDX_6354059C4663E4 ON files (page_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
    }
}
