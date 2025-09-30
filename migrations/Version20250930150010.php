<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930150010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, numero_facture VARCHAR(50) NOT NULL, date_facture DATE NOT NULL, type_facture VARCHAR(10) NOT NULL, devise VARCHAR(3) NOT NULL, nom_fournisseur VARCHAR(255) NOT NULL, siren_fournisseur VARCHAR(20) NOT NULL, siret_fournisseur VARCHAR(20) DEFAULT NULL, tva_fournisseur VARCHAR(20) DEFAULT NULL, code_pays_fournisseur VARCHAR(2) NOT NULL, email_fournisseur VARCHAR(255) NOT NULL, nom_acheteur VARCHAR(255) NOT NULL, siren_acheteur VARCHAR(20) NOT NULL, email_acheteur VARCHAR(255) NOT NULL, commande_acheteur VARCHAR(20) DEFAULT NULL, total_ht NUMERIC(10, 2) NOT NULL, total_tva NUMERIC(10, 2) NOT NULL, total_ttc NUMERIC(10, 2) NOT NULL, net_apayer NUMERIC(10, 2) NOT NULL, date_echeance DATE DEFAULT NULL, date_livraison DATE DEFAULT NULL, mode_paiement VARCHAR(10) DEFAULT NULL, reference_paiement VARCHAR(100) DEFAULT NULL, tva_details JSON DEFAULT NULL, remise_pied NUMERIC(10, 2) DEFAULT NULL, charges_pied NUMERIC(10, 2) DEFAULT NULL, reference_contrat VARCHAR(255) DEFAULT NULL, reference_bon_livraison VARCHAR(255) DEFAULT NULL, profil_factur_x VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE facture');
    }
}
