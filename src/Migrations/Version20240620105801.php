<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240620105801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collection_points DROP FOREIGN KEY FK_D59F76DF7CBFB62');
        $this->addSql('DROP INDEX IDX_D59F76DF7CBFB62 ON collection_points');
        $this->addSql('ALTER TABLE collection_points CHANGE alternative_collection_point_id collection_point_alternative_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE collection_points ADD CONSTRAINT FK_D59F76DFB98B0292 FOREIGN KEY (collection_point_alternative_id) REFERENCES collection_points (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D59F76DFB98B0292 ON collection_points (collection_point_alternative_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type ENUM(\'work\', \'collect\')');
        $this->addSql('ALTER TABLE settings_to_roles DROP FOREIGN KEY FK_9B652BF3ADA40271');
        $this->addSql('DROP INDEX IDX_9B652BF3ADA40271 ON settings_to_roles');
        $this->addSql('DROP INDEX `primary` ON settings_to_roles');
        $this->addSql('ALTER TABLE settings_to_roles CHANGE link_id setting_id INT NOT NULL');
        $this->addSql('ALTER TABLE settings_to_roles ADD CONSTRAINT FK_9B652BF3EE35BD72 FOREIGN KEY (setting_id) REFERENCES settings (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9B652BF3EE35BD72 ON settings_to_roles (setting_id)');
        $this->addSql('ALTER TABLE settings_to_roles ADD PRIMARY KEY (setting_id, role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE settings_to_roles DROP FOREIGN KEY FK_9B652BF3EE35BD72');
        $this->addSql('DROP INDEX IDX_9B652BF3EE35BD72 ON settings_to_roles');
        $this->addSql('DROP INDEX `PRIMARY` ON settings_to_roles');
        $this->addSql('ALTER TABLE settings_to_roles CHANGE setting_id link_id INT NOT NULL');
        $this->addSql('ALTER TABLE settings_to_roles ADD CONSTRAINT FK_9B652BF3ADA40271 FOREIGN KEY (link_id) REFERENCES settings (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9B652BF3ADA40271 ON settings_to_roles (link_id)');
        $this->addSql('ALTER TABLE settings_to_roles ADD PRIMARY KEY (link_id, role_id)');
        $this->addSql('ALTER TABLE periods CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE collection_points DROP FOREIGN KEY FK_D59F76DFB98B0292');
        $this->addSql('DROP INDEX IDX_D59F76DFB98B0292 ON collection_points');
        $this->addSql('ALTER TABLE collection_points CHANGE collection_point_alternative_id alternative_collection_point_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE collection_points ADD CONSTRAINT FK_D59F76DF7CBFB62 FOREIGN KEY (alternative_collection_point_id) REFERENCES collection_points (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D59F76DF7CBFB62 ON collection_points (alternative_collection_point_id)');
    }
}
