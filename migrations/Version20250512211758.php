<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512211758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE liste DROP FOREIGN KEY FK_FCF22AF49D86650F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_FCF22AF49D86650F ON liste
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE liste CHANGE user_id_id user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE liste ADD CONSTRAINT FK_FCF22AF4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FCF22AF4A76ED395 ON liste (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE liste DROP FOREIGN KEY FK_FCF22AF4A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_FCF22AF4A76ED395 ON liste
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE liste CHANGE user_id user_id_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE liste ADD CONSTRAINT FK_FCF22AF49D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FCF22AF49D86650F ON liste (user_id_id)
        SQL);
    }
}
