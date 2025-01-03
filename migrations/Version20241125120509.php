<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125120509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation ADD image_size INT DEFAULT NULL, ADD update_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE picture image_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD image_size INT DEFAULT NULL, CHANGE picture image_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation DROP image_size, DROP update_at, CHANGE image_name picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP image_size, CHANGE image_name picture VARCHAR(255) DEFAULT NULL');
    }
}
