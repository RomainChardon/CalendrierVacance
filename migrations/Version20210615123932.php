<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210615123932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groupe ADD COLUMN admin BOOLEAN NOT NULL');
        $this->addSql('DROP INDEX IDX_8D93D6497A45358C');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, groupe_id, username, roles, password, nom, prenom FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, groupe_id INTEGER DEFAULT NULL, username VARCHAR(180) NOT NULL COLLATE BINARY, roles CLOB NOT NULL COLLATE BINARY --(DC2Type:json)
        , password VARCHAR(255) NOT NULL COLLATE BINARY, nom VARCHAR(255) NOT NULL COLLATE BINARY, prenom VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_8D93D6497A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user (id, groupe_id, username, roles, password, nom, prenom) SELECT id, groupe_id, username, roles, password, nom, prenom FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE INDEX IDX_8D93D6497A45358C ON user (groupe_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('DROP INDEX IDX_A88D8D0BDE2BFA77');
        $this->addSql('DROP INDEX IDX_A88D8D0BA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_vacances AS SELECT user_id, vacances_id FROM user_vacances');
        $this->addSql('DROP TABLE user_vacances');
        $this->addSql('CREATE TABLE user_vacances (user_id INTEGER NOT NULL, vacances_id INTEGER NOT NULL, PRIMARY KEY(user_id, vacances_id), CONSTRAINT FK_A88D8D0BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A88D8D0BDE2BFA77 FOREIGN KEY (vacances_id) REFERENCES vacances (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_vacances (user_id, vacances_id) SELECT user_id, vacances_id FROM __temp__user_vacances');
        $this->addSql('DROP TABLE __temp__user_vacances');
        $this->addSql('CREATE INDEX IDX_A88D8D0BDE2BFA77 ON user_vacances (vacances_id)');
        $this->addSql('CREATE INDEX IDX_A88D8D0BA76ED395 ON user_vacances (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__groupe AS SELECT id, nom_groupe, couleur FROM groupe');
        $this->addSql('DROP TABLE groupe');
        $this->addSql('CREATE TABLE groupe (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom_groupe VARCHAR(255) NOT NULL, couleur VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO groupe (id, nom_groupe, couleur) SELECT id, nom_groupe, couleur FROM __temp__groupe');
        $this->addSql('DROP TABLE __temp__groupe');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677');
        $this->addSql('DROP INDEX IDX_8D93D6497A45358C');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, groupe_id, username, roles, password, nom, prenom FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, groupe_id INTEGER DEFAULT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, groupe_id, username, roles, password, nom, prenom) SELECT id, groupe_id, username, roles, password, nom, prenom FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE INDEX IDX_8D93D6497A45358C ON user (groupe_id)');
        $this->addSql('DROP INDEX IDX_A88D8D0BA76ED395');
        $this->addSql('DROP INDEX IDX_A88D8D0BDE2BFA77');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_vacances AS SELECT user_id, vacances_id FROM user_vacances');
        $this->addSql('DROP TABLE user_vacances');
        $this->addSql('CREATE TABLE user_vacances (user_id INTEGER NOT NULL, vacances_id INTEGER NOT NULL, PRIMARY KEY(user_id, vacances_id))');
        $this->addSql('INSERT INTO user_vacances (user_id, vacances_id) SELECT user_id, vacances_id FROM __temp__user_vacances');
        $this->addSql('DROP TABLE __temp__user_vacances');
        $this->addSql('CREATE INDEX IDX_A88D8D0BA76ED395 ON user_vacances (user_id)');
        $this->addSql('CREATE INDEX IDX_A88D8D0BDE2BFA77 ON user_vacances (vacances_id)');
    }
}
