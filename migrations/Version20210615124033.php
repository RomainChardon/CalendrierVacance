<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210615124033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groupe (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom_groupe VARCHAR(255) NOT NULL, couleur VARCHAR(255) DEFAULT NULL, admin BOOLEAN NOT NULL)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, groupe_id INTEGER DEFAULT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE INDEX IDX_8D93D6497A45358C ON user (groupe_id)');
        $this->addSql('CREATE TABLE user_vacances (user_id INTEGER NOT NULL, vacances_id INTEGER NOT NULL, PRIMARY KEY(user_id, vacances_id))');
        $this->addSql('CREATE INDEX IDX_A88D8D0BA76ED395 ON user_vacances (user_id)');
        $this->addSql('CREATE INDEX IDX_A88D8D0BDE2BFA77 ON user_vacances (vacances_id)');
        $this->addSql('CREATE TABLE vacances (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, autoriser BOOLEAN NOT NULL, heure_debut DATETIME NOT NULL, heure_fin DATETIME NOT NULL, attente BOOLEAN NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE groupe');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_vacances');
        $this->addSql('DROP TABLE vacances');
    }
}
