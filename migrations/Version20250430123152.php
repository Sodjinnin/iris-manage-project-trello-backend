<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430123152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_527edb25483442de
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_527edb25b03a8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task ADD priority VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task ADD schedule_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_527EDB25B03A8386 ON task (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_527EDB25483442DE ON task (assign_to_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_527EDB25B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_527EDB25483442DE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "task" DROP priority
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "task" DROP schedule_date
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_527edb25483442de ON "task" (assign_to_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_527edb25b03a8386 ON "task" (created_by_id)
        SQL);
    }
}
