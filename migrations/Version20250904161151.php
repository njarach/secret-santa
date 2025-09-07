<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250904161151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE draw (id SERIAL NOT NULL, event_id INT NOT NULL, giver_id INT NOT NULL, receiver_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_70F2BD0F71F7E88B ON draw (event_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70F2BD0F75BD1D29 ON draw (giver_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70F2BD0FCD53EDB6 ON draw (receiver_id)');
        $this->addSql('COMMENT ON COLUMN draw.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE event (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, budget DOUBLE PRECISION DEFAULT NULL, drawn_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, admin_email VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN event.drawn_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE participant (id SERIAL NOT NULL, event_id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, wishlist TEXT DEFAULT NULL, exclusions TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D79F6B1171F7E88B ON participant (event_id)');
        $this->addSql('COMMENT ON COLUMN participant.exclusions IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN participant.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE draw ADD CONSTRAINT FK_70F2BD0F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE draw ADD CONSTRAINT FK_70F2BD0F75BD1D29 FOREIGN KEY (giver_id) REFERENCES participant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE draw ADD CONSTRAINT FK_70F2BD0FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES participant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B1171F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE draw DROP CONSTRAINT FK_70F2BD0F71F7E88B');
        $this->addSql('ALTER TABLE draw DROP CONSTRAINT FK_70F2BD0F75BD1D29');
        $this->addSql('ALTER TABLE draw DROP CONSTRAINT FK_70F2BD0FCD53EDB6');
        $this->addSql('ALTER TABLE participant DROP CONSTRAINT FK_D79F6B1171F7E88B');
        $this->addSql('DROP TABLE draw');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE participant');
    }
}
