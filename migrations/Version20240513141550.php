<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240513141550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme ADD dpd_avg28 INT NOT NULL, ADD dpd_avg7 INT NOT NULL, ADD dpd_last_updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE theme_author DROP author, DROP author_url');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme DROP dpd_avg28, DROP dpd_avg7, DROP dpd_last_updated');
        $this->addSql('ALTER TABLE theme_author ADD author VARCHAR(255) DEFAULT NULL, ADD author_url VARCHAR(255) DEFAULT NULL');
    }
}
