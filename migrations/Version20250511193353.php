<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511193353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE list_film (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_filtre ADD title VARCHAR(499) DEFAULT NULL, ADD synopsis LONGTEXT DEFAULT NULL, ADD important_crew LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', ADD actors VARCHAR(500) DEFAULT NULL, ADD poster_path VARCHAR(255) DEFAULT NULL, ADD runtime_minutes INT DEFAULT NULL, ADD tmdb_rating DOUBLE PRECISION DEFAULT NULL, ADD num_votes INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE list_film
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_filtre DROP title, DROP synopsis, DROP important_crew, DROP actors, DROP poster_path, DROP runtime_minutes, DROP tmdb_rating, DROP num_votes
        SQL);
    }
}
