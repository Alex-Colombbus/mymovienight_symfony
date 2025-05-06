<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506132920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE film ADD PRIMARY KEY (tconst)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_filtre ADD PRIMARY KEY (tconst)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_filtre ADD CONSTRAINT FK_926663468D957D86 FOREIGN KEY (tconst) REFERENCES film (tconst)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON film
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_filtre DROP FOREIGN KEY FK_926663468D957D86
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON film_filtre
        SQL);
    }
}
