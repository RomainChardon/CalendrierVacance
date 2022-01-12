<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220112160428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD mail VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE vacances ADD demi_journee VARCHAR(255) DEFAULT NULL, ADD sans_soldes TINYINT(1) DEFAULT NULL, DROP heure_debut, DROP heure_fin');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP mail');
        $this->addSql('ALTER TABLE vacances ADD heure_debut DATETIME NOT NULL, ADD heure_fin DATETIME NOT NULL, DROP demi_journee, DROP sans_soldes');
    }
}
