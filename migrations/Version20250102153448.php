<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250102153448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_facture ADD contact_id INT NOT NULL');
        $this->addSql('ALTER TABLE client_facture ADD CONSTRAINT FK_ACBB206DE7A1254A FOREIGN KEY (contact_id) REFERENCES client_contact (id)');
        $this->addSql('CREATE INDEX IDX_ACBB206DE7A1254A ON client_facture (contact_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_facture DROP FOREIGN KEY FK_ACBB206DE7A1254A');
        $this->addSql('DROP INDEX IDX_ACBB206DE7A1254A ON client_facture');
        $this->addSql('ALTER TABLE client_facture DROP contact_id');
    }
}
