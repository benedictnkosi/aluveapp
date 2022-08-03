<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718141910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE add_ons CHANGE property property INT DEFAULT NULL');
        $this->addSql('ALTER TABLE employee CHANGE property property INT DEFAULT NULL');
        $this->addSql('ALTER TABLE guest CHANGE property property INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ical CHANGE room room INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message_template CHANGE property property INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservations CHANGE uid uid VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE rooms CHANGE property property INT DEFAULT NULL, CHANGE tv tv INT DEFAULT NULL');
        $this->addSql('ALTER TABLE schedule_messages CHANGE room room INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE add_ons CHANGE property property INT NOT NULL');
        $this->addSql('ALTER TABLE employee CHANGE property property INT NOT NULL');
        $this->addSql('ALTER TABLE guest CHANGE property property INT NOT NULL');
        $this->addSql('ALTER TABLE ical CHANGE room room INT NOT NULL');
        $this->addSql('ALTER TABLE message_template CHANGE property property INT NOT NULL');
        $this->addSql('ALTER TABLE reservations CHANGE uid uid VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE rooms CHANGE tv tv INT NOT NULL, CHANGE property property INT NOT NULL');
        $this->addSql('ALTER TABLE schedule_messages CHANGE room room INT NOT NULL');
    }
}
