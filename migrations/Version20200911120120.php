<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200911120120 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE client ADD suspendu VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455ABE530DA ON client (cin)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455176A13E1 ON client (image_1)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404558E63425B ON client (image_2)');
        $this->addSql('DROP INDEX client_id ON pointage');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_C7440455ABE530DA ON client');
        $this->addSql('DROP INDEX UNIQ_C7440455176A13E1 ON client');
        $this->addSql('DROP INDEX UNIQ_C74404558E63425B ON client');
        $this->addSql('ALTER TABLE client DROP suspendu');
        $this->addSql('CREATE INDEX client_id ON pointage (client_id)');
    }
}
