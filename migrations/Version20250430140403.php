<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430140403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE "task" (id INT NOT NULL, created_by_id INT NOT NULL, assign_to_id INT DEFAULT NULL, project_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, priority VARCHAR(255) DEFAULT NULL, schedule_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_sub BOOLEAN DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_527EDB25B03A8386 ON "task" (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_527EDB25483442DE ON "task" (assign_to_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_527EDB25166D1F9C ON "task" (project_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "task" ADD CONSTRAINT FK_527EDB25B03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "task" ADD CONSTRAINT FK_527EDB25483442DE FOREIGN KEY (assign_to_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "task" ADD CONSTRAINT FK_527EDB25166D1F9C FOREIGN KEY (project_id) REFERENCES "project" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_task ADD CONSTRAINT FK_21CD4F5E6423FBA0 FOREIGN KEY (task_source) REFERENCES "task" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_task ADD CONSTRAINT FK_21CD4F5E7DC6AB2F FOREIGN KEY (task_target) REFERENCES "task" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_task DROP CONSTRAINT FK_21CD4F5E6423FBA0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_task DROP CONSTRAINT FK_21CD4F5E7DC6AB2F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "task" DROP CONSTRAINT FK_527EDB25B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "task" DROP CONSTRAINT FK_527EDB25483442DE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "task" DROP CONSTRAINT FK_527EDB25166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "task"
        SQL);
    }
}
