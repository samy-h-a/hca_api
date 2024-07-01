<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240624005949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE downloads DROP CONSTRAINT fk_4b73a4b59d86650f');
        $this->addSql('DROP INDEX uniq_4b73a4b59d86650f');
        $this->addSql('ALTER TABLE downloads RENAME COLUMN user_id_id TO user_id');
        $this->addSql('ALTER TABLE downloads ADD CONSTRAINT FK_4B73A4B5A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B73A4B5A76ED395 ON downloads (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE downloads DROP CONSTRAINT FK_4B73A4B5A76ED395');
        $this->addSql('DROP INDEX UNIQ_4B73A4B5A76ED395');
        $this->addSql('ALTER TABLE downloads RENAME COLUMN user_id TO user_id_id');
        $this->addSql('ALTER TABLE downloads ADD CONSTRAINT fk_4b73a4b59d86650f FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_4b73a4b59d86650f ON downloads (user_id_id)');
    }
}
