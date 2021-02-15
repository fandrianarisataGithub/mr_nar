<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210128094344 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, cin VARCHAR(255) NOT NULL, image_1 VARCHAR(255) NOT NULL, image_2 VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, montant INT NOT NULL, montant_mensuel INT NOT NULL, nbr_versement INT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, matricule VARCHAR(255) NOT NULL, verifier VARCHAR(255) NOT NULL, numero_bl VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, etat_client VARCHAR(255) NOT NULL, numero_pointage INT NOT NULL, tab_pointage LONGTEXT NOT NULL, nom_pointage_av VARCHAR(255) NOT NULL, budget VARCHAR(255) NOT NULL, article VARCHAR(255) NOT NULL, chapitre VARCHAR(255) NOT NULL, INDEX IDX_C7440455A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichier1 (id INT AUTO_INCREMENT NOT NULL, num_pens VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, arr_ssd VARCHAR(255) DEFAULT NULL, date VARCHAR(255) DEFAULT NULL, ben VARCHAR(255) DEFAULT NULL, ord VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichier2 (id INT AUTO_INCREMENT NOT NULL, num_pens VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, rub VARCHAR(255) DEFAULT NULL, montant VARCHAR(255) DEFAULT NULL, date_debut VARCHAR(255) DEFAULT NULL, date_fin VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichier3 (id INT AUTO_INCREMENT NOT NULL, num_pens VARCHAR(255) DEFAULT NULL, code_rub VARCHAR(255) DEFAULT NULL, ben VARCHAR(255) DEFAULT NULL, montant VARCHAR(255) DEFAULT NULL, date_fin VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pointage (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, nom VARCHAR(255) NOT NULL, nom_lit VARCHAR(255) NOT NULL, nom_user VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pointage_client (pointage_id INT NOT NULL, client_id INT NOT NULL, INDEX IDX_300FBD11E58DA11D (pointage_id), INDEX IDX_300FBD1119EB6921 (client_id), PRIMARY KEY(pointage_id, client_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, usernmane VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, cin VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649817B5FD4 (usernmane), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE pointage_client ADD CONSTRAINT FK_300FBD11E58DA11D FOREIGN KEY (pointage_id) REFERENCES pointage (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pointage_client ADD CONSTRAINT FK_300FBD1119EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pointage_client DROP FOREIGN KEY FK_300FBD1119EB6921');
        $this->addSql('ALTER TABLE pointage_client DROP FOREIGN KEY FK_300FBD11E58DA11D');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455A76ED395');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE fichier1');
        $this->addSql('DROP TABLE fichier2');
        $this->addSql('DROP TABLE fichier3');
        $this->addSql('DROP TABLE pointage');
        $this->addSql('DROP TABLE pointage_client');
        $this->addSql('DROP TABLE user');
    }
}
