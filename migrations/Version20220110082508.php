<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220110082508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groupe (id INT AUTO_INCREMENT NOT NULL, nom_groupe VARCHAR(255) NOT NULL, couleur VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, groupe_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), INDEX IDX_8D93D6497A45358C (groupe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_vacances (user_id INT NOT NULL, vacances_id INT NOT NULL, INDEX IDX_A88D8D0BA76ED395 (user_id), INDEX IDX_A88D8D0BDE2BFA77 (vacances_id), PRIMARY KEY(user_id, vacances_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vacances (id INT AUTO_INCREMENT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, autoriser TINYINT(1) NOT NULL, heure_debut DATETIME NOT NULL, heure_fin DATETIME NOT NULL, attente TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id)');
        $this->addSql('ALTER TABLE user_vacances ADD CONSTRAINT FK_A88D8D0BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_vacances ADD CONSTRAINT FK_A88D8D0BDE2BFA77 FOREIGN KEY (vacances_id) REFERENCES vacances (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497A45358C');
        $this->addSql('ALTER TABLE user_vacances DROP FOREIGN KEY FK_A88D8D0BA76ED395');
        $this->addSql('ALTER TABLE user_vacances DROP FOREIGN KEY FK_A88D8D0BDE2BFA77');
        $this->addSql('DROP TABLE groupe');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_vacances');
        $this->addSql('DROP TABLE vacances');
    }
}
