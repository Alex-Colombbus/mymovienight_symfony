<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504223316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE film (tconst VARCHAR(15) NOT NULL, title_type VARCHAR(30) NOT NULL, primary_title VARCHAR(499) NOT NULL, original_title VARCHAR(499) NOT NULL, is_adult TINYINT(1) DEFAULT NULL, start_year INT DEFAULT NULL, end_year INT DEFAULT NULL, runtime_minutes INT DEFAULT NULL, genres VARCHAR(255) DEFAULT NULL, PRIMARY KEY(tconst)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE rating (tconst VARCHAR(15) NOT NULL, average_rating NUMERIC(3, 1) DEFAULT NULL, num_votes INT DEFAULT NULL, PRIMARY KEY(tconst)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE film
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rating
        SQL);
    }
}
