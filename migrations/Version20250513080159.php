<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513080159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C78A61CBA6D70A54 ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film CHANGE list_id_id liste_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD CONSTRAINT FK_C78A61CBE85441D8 FOREIGN KEY (liste_id) REFERENCES liste (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C78A61CBE85441D8 ON list_film (liste_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_tconst_liste ON list_film (tconst, liste_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD PRIMARY KEY (tconst, liste_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film DROP FOREIGN KEY FK_C78A61CBE85441D8
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C78A61CBE85441D8 ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX unique_tconst_liste ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON list_film
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film CHANGE liste_id list_id_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C78A61CBA6D70A54 ON list_film (list_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD PRIMARY KEY (tconst, list_id_id)
        SQL);
    }
}
