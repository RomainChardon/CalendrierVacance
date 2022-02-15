<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220214080633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groupe CHANGE couleur couleur VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE groupe_id groupe_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE nb_conges nb_conges DOUBLE PRECISION DEFAULT NULL, CHANGE cadre cadre TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE vacances ADD user_id INT DEFAULT NULL, CHANGE maladie maladie TINYINT(1) DEFAULT NULL, CHANGE demi_journee demi_journee VARCHAR(255) DEFAULT NULL, CHANGE sans_soldes sans_soldes TINYINT(1) DEFAULT NULL, CHANGE rtt rtt TINYINT(1) DEFAULT NULL, CHANGE annuler annuler TINYINT(1) DEFAULT NULL, CHANGE date_demande date_demande DATETIME DEFAULT NULL, CHANGE date_annulation date_annulation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE vacances ADD CONSTRAINT FK_4800690BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_4800690BA76ED395 ON vacances (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groupe CHANGE couleur couleur VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE groupe_id groupe_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE nb_conges nb_conges DOUBLE PRECISION DEFAULT \'NULL\', CHANGE cadre cadre TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE vacances DROP FOREIGN KEY FK_4800690BA76ED395');
        $this->addSql('DROP INDEX IDX_4800690BA76ED395 ON vacances');
        $this->addSql('ALTER TABLE vacances DROP user_id, CHANGE maladie maladie TINYINT(1) DEFAULT NULL, CHANGE demi_journee demi_journee VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE sans_soldes sans_soldes TINYINT(1) DEFAULT NULL, CHANGE rtt rtt TINYINT(1) DEFAULT NULL, CHANGE annuler annuler TINYINT(1) DEFAULT NULL, CHANGE date_demande date_demande DATETIME DEFAULT \'NULL\', CHANGE date_annulation date_annulation DATETIME DEFAULT \'NULL\'');
    }
}
