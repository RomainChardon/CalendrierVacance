<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220126092445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD cadre TINYINT(1) DEFAULT NULL, DROP desactiver');
        $this->addSql('ALTER TABLE vacances ADD annuler TINYINT(1) DEFAULT NULL, ADD date_demande DATETIME DEFAULT NULL, ADD date_annulation DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD desactiver TINYINT(1) NOT NULL, DROP cadre');
        $this->addSql('ALTER TABLE vacances DROP annuler, DROP date_demande, DROP date_annulation');
    }
}
