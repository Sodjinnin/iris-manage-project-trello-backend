<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527082544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE task_task (task_source INT NOT NULL, task_target INT NOT NULL, INDEX IDX_21CD4F5E6423FBA0 (task_source), INDEX IDX_21CD4F5E7DC6AB2F (task_target), PRIMARY KEY(task_source, task_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_task ADD CONSTRAINT FK_21CD4F5E6423FBA0 FOREIGN KEY (task_source) REFERENCES `task` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_task ADD CONSTRAINT FK_21CD4F5E7DC6AB2F FOREIGN KEY (task_target) REFERENCES `task` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task ADD is_sub TINYINT(1) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE task_task DROP FOREIGN KEY FK_21CD4F5E6423FBA0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_task DROP FOREIGN KEY FK_21CD4F5E7DC6AB2F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE task_task
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `task` DROP is_sub
        SQL);
    }
}
