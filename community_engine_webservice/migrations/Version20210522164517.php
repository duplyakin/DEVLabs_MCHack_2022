<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210522164517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE metric_order RENAME COLUMN value TO with_user_id');
        $this->addSql('ALTER TABLE metric_order ADD CONSTRAINT FK_76756C0DAE83ED76 FOREIGN KEY (with_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_76756C0DAE83ED76 ON metric_order (with_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE metric_order DROP CONSTRAINT FK_76756C0DAE83ED76');
        $this->addSql('DROP INDEX IDX_76756C0DAE83ED76');
        $this->addSql('ALTER TABLE metric_order RENAME COLUMN with_user_id TO value');
    }
}
