<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250523130605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE film_genre (film_tconst VARCHAR(15) NOT NULL, genre_id INT NOT NULL, INDEX IDX_1A3CCDA8E667408C (film_tconst), INDEX IDX_1A3CCDA84296D31F (genre_id), PRIMARY KEY(film_tconst, genre_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_genre ADD CONSTRAINT FK_1A3CCDA8E667408C FOREIGN KEY (film_tconst) REFERENCES film_filtre (tconst)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_genre ADD CONSTRAINT FK_1A3CCDA84296D31F FOREIGN KEY (genre_id) REFERENCES genre (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE film_genre DROP FOREIGN KEY FK_1A3CCDA8E667408C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_genre DROP FOREIGN KEY FK_1A3CCDA84296D31F
        SQL);
        // $this->addSql(<<<'SQL'
        //     DROP TABLE film_genre
        // SQL);
    }
}
