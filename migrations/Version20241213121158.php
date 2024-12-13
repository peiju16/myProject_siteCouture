<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213121158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD transport_way_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993983F7B449A FOREIGN KEY (transport_way_id) REFERENCES transport (id)');
        $this->addSql('CREATE INDEX IDX_F52993983F7B449A ON `order` (transport_way_id)');
        $this->addSql('ALTER TABLE transport ADD is_pickup TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993983F7B449A');
        $this->addSql('DROP INDEX IDX_F52993983F7B449A ON `order`');
        $this->addSql('ALTER TABLE `order` DROP transport_way_id');
        $this->addSql('ALTER TABLE transport DROP is_pickup');
    }
}
