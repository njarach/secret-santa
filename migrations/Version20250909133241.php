<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909133241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD admin_access_token VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE event ADD public_join_token VARCHAR(16) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7CFE37F85 ON event (admin_access_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA74CE746FB ON event (public_join_token)');
        $this->addSql('ALTER TABLE participant ADD event_access_token VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE participant ALTER exclusions TYPE JSON');
        $this->addSql('COMMENT ON COLUMN participant.exclusions IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7CFE37F85');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA74CE746FB');
        $this->addSql('ALTER TABLE event DROP admin_access_token');
        $this->addSql('ALTER TABLE event DROP public_join_token');
        $this->addSql('ALTER TABLE participant DROP event_access_token');
        $this->addSql('ALTER TABLE participant ALTER exclusions TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN participant.exclusions IS \'(DC2Type:array)\'');
    }
}
