<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520132626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE film_filtre CHANGE important_crew important_crew JSON DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_title ON film_filtre (title)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_start_year ON film_filtre (start_year)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_genres ON film_filtre (genres)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_average_rating ON film_filtre (average_rating)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_num_votes ON film_filtre (num_votes)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_title_type ON film_filtre (title_type)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX idx_title ON film_filtre
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_start_year ON film_filtre
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_genres ON film_filtre
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_average_rating ON film_filtre
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_num_votes ON film_filtre
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_title_type ON film_filtre
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE film_filtre CHANGE important_crew important_crew LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)'
        SQL);
    }
}
