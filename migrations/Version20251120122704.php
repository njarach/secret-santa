<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120122704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE draw (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, giver_id INT NOT NULL, receiver_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_70F2BD0F71F7E88B (event_id), UNIQUE INDEX UNIQ_70F2BD0F75BD1D29 (giver_id), UNIQUE INDEX UNIQ_70F2BD0FCD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, budget DOUBLE PRECISION DEFAULT NULL, drawn_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', admin_email VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, admin_access_token VARCHAR(64) NOT NULL, verification_token VARCHAR(64) DEFAULT NULL, verification_sent_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', verified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_3BAE0AA7CFE37F85 (admin_access_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, wishlist LONGTEXT DEFAULT NULL, exclusions JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', event_access_token VARCHAR(64) NOT NULL, access_token_expire_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D79F6B1171F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE draw ADD CONSTRAINT FK_70F2BD0F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE draw ADD CONSTRAINT FK_70F2BD0F75BD1D29 FOREIGN KEY (giver_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE draw ADD CONSTRAINT FK_70F2BD0FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B1171F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE draw DROP FOREIGN KEY FK_70F2BD0F71F7E88B');
        $this->addSql('ALTER TABLE draw DROP FOREIGN KEY FK_70F2BD0F75BD1D29');
        $this->addSql('ALTER TABLE draw DROP FOREIGN KEY FK_70F2BD0FCD53EDB6');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B1171F7E88B');
        $this->addSql('DROP TABLE draw');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE participant');
    }
}
