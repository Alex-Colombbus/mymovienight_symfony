<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250509145703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE film (tconst VARCHAR(15) NOT NULL, primary_title VARCHAR(499) NOT NULL, original_title VARCHAR(499) NOT NULL, end_year INT DEFAULT NULL, runtime_minutes INT DEFAULT NULL, num_votes INT DEFAULT NULL, PRIMARY KEY(tconst)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE film_filtre (tconst VARCHAR(15) NOT NULL, title_type VARCHAR(30) NOT NULL, is_adult TINYINT(1) NOT NULL, start_year INT DEFAULT NULL, genres LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', average_rating NUMERIC(3, 1) DEFAULT NULL, PRIMARY KEY(tconst)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_filtre ADD CONSTRAINT FK_926663468D957D86 FOREIGN KEY (tconst) REFERENCES film (tconst)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE film_filtre DROP FOREIGN KEY FK_926663468D957D86
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE film
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE film_filtre
        SQL);
    }
}
