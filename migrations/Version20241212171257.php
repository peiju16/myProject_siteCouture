<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241212171257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transport_address (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(50) NOT NULL, address VARCHAR(255) NOT NULL, city VARCHAR(25) NOT NULL, zip_code VARCHAR(10) NOT NULL, telephone VARCHAR(25) NOT NULL, INDEX IDX_9999AF70A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transport_address ADD CONSTRAINT FK_9999AF70A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transport CHANGE price price NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transport_address DROP FOREIGN KEY FK_9999AF70A76ED395');
        $this->addSql('DROP TABLE transport_address');
        $this->addSql('ALTER TABLE transport CHANGE price price DOUBLE PRECISION NOT NULL');
    }
}
