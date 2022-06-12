<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210607130529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manager_community (user_id INT NOT NULL, community_id INT NOT NULL, PRIMARY KEY(user_id, community_id))');
        $this->addSql('CREATE INDEX IDX_1B80CDD7A76ED395 ON manager_community (user_id)');
        $this->addSql('CREATE INDEX IDX_1B80CDD7FDA7B0BF ON manager_community (community_id)');
        $this->addSql('ALTER TABLE manager_community ADD CONSTRAINT FK_1B80CDD7A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE manager_community ADD CONSTRAINT FK_1B80CDD7FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_1B80CDD7A76ED395');
        $this->addSql('DROP INDEX IDX_1B80CDD7FDA7B0BF');
        $this->addSql('DROP TABLE manager_community');
    }
}
