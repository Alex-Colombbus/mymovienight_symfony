<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512204045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE list_film (tconst VARCHAR(15) NOT NULL, list_id_id INT NOT NULL, added_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_C78A61CB8D957D86 (tconst), INDEX IDX_C78A61CBA6D70A54 (list_id_id), PRIMARY KEY(tconst, list_id_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD CONSTRAINT FK_C78A61CB8D957D86 FOREIGN KEY (tconst) REFERENCES film_filtre (tconst)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film ADD CONSTRAINT FK_C78A61CBA6D70A54 FOREIGN KEY (list_id_id) REFERENCES liste (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film DROP FOREIGN KEY FK_C78A61CB8D957D86
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE list_film DROP FOREIGN KEY FK_C78A61CBA6D70A54
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE list_film
        SQL);
    }
}
