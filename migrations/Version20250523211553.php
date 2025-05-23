<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250523211553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_835033F86C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_genre ADD PRIMARY KEY (film_tconst, genre_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_genre ADD CONSTRAINT FK_1A3CCDA8E667408C FOREIGN KEY (film_tconst) REFERENCES film_filtre (tconst)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_genre ADD CONSTRAINT FK_1A3CCDA84296D31F FOREIGN KEY (genre_id) REFERENCES genre (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD CONSTRAINT FK_C78A61CB990DC2FF FOREIGN KEY (tconst_id) REFERENCES film_filtre (tconst)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD CONSTRAINT FK_C78A61CBE85441D8 FOREIGN KEY (liste_id) REFERENCES liste (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_film_in_list ON list_film (tconst_id, liste_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE film_genre DROP FOREIGN KEY FK_1A3CCDA84296D31F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE genre
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_genre DROP FOREIGN KEY FK_1A3CCDA8E667408C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON film_genre
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film DROP FOREIGN KEY FK_C78A61CB990DC2FF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film DROP FOREIGN KEY FK_C78A61CBE85441D8
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX unique_film_in_list ON list_film
        SQL);
    }
}
