<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715154001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE book ADD category_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE book DROP category
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE book ADD CONSTRAINT FK_CBE5A33112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CBE5A33112469DE2 ON book (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ALTER id DROP DEFAULT
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE category_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('category_id_seq', (SELECT MAX(id) FROM category))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ALTER id SET DEFAULT nextval('category_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE book DROP CONSTRAINT FK_CBE5A33112469DE2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CBE5A33112469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE book ADD category VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE book DROP category_id
        SQL);
    }
}
