<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430124241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_527edb25166d1f9c
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_527EDB25166D1F9C ON task (project_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_527EDB25166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_527edb25166d1f9c ON "task" (project_id)
        SQL);
    }
}
