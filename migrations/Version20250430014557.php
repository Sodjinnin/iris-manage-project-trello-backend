<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430014557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE project_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE project (id INT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2FB3D0EE7E3C61F9 ON project (owner_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN project.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN project.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE project_user (project_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(project_id, user_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B4021E51166D1F9C ON project_user (project_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B4021E51A76ED395 ON project_user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id INT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, api_token TEXT DEFAULT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, user_type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE project_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE user_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE7E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_user DROP CONSTRAINT FK_B4021E51166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_user DROP CONSTRAINT FK_B4021E51A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE project
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE project_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
