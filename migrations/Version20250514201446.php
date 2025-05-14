<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514201446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE film
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film DROP FOREIGN KEY FK_C78A61CB8D957D86
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C78A61CB8D957D86 ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX unique_tconst_liste ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD id INT AUTO_INCREMENT NOT NULL, CHANGE tconst tconst_id VARCHAR(15) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD CONSTRAINT FK_C78A61CB990DC2FF FOREIGN KEY (tconst_id) REFERENCES film_filtre (tconst)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C78A61CB990DC2FF ON list_film (tconst_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_film_in_list ON list_film (tconst_id, liste_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE film (tconst VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, primary_title VARCHAR(499) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, original_title VARCHAR(499) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, num_votes INT DEFAULT NULL, end_year INT DEFAULT NULL, runtime_minutes INT DEFAULT NULL, PRIMARY KEY(tconst)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film MODIFY id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film DROP FOREIGN KEY FK_C78A61CB990DC2FF
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C78A61CB990DC2FF ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX unique_film_in_list ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film DROP id, CHANGE tconst_id tconst VARCHAR(15) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD CONSTRAINT FK_C78A61CB8D957D86 FOREIGN KEY (tconst) REFERENCES film_filtre (tconst) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C78A61CB8D957D86 ON list_film (tconst)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_tconst_liste ON list_film (tconst, liste_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD PRIMARY KEY (tconst, liste_id)
        SQL);
    }
}
