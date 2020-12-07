<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201207070231 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE fichier1 (id INT AUTO_INCREMENT NOT NULL, num_pens VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, arr_ssd VARCHAR(255) DEFAULT NULL, date VARCHAR(255) DEFAULT NULL, ben VARCHAR(255) DEFAULT NULL, ord VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichier2 (id INT AUTO_INCREMENT NOT NULL, num_pens VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, rub VARCHAR(255) DEFAULT NULL, montant VARCHAR(255) DEFAULT NULL, date_debut VARCHAR(255) DEFAULT NULL, date_fin VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichier3 (id INT AUTO_INCREMENT NOT NULL, num_pens VARCHAR(255) DEFAULT NULL, code_rub VARCHAR(255) DEFAULT NULL, ben VARCHAR(255) DEFAULT NULL, montant VARCHAR(255) DEFAULT NULL, date_fin VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE fichier1');
        $this->addSql('DROP TABLE fichier2');
        $this->addSql('DROP TABLE fichier3');
    }
}
