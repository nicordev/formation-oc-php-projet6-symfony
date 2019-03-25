<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190325141208 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE trick_group_trick (trick_group_id INT NOT NULL, trick_id INT NOT NULL, INDEX IDX_6EBE5109B875DF8 (trick_group_id), INDEX IDX_6EBE510B281BE2E (trick_id), PRIMARY KEY(trick_group_id, trick_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE trick_group_trick ADD CONSTRAINT FK_6EBE5109B875DF8 FOREIGN KEY (trick_group_id) REFERENCES trick_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trick_group_trick ADD CONSTRAINT FK_6EBE510B281BE2E FOREIGN KEY (trick_id) REFERENCES trick (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91E9B875DF8');
        $this->addSql('DROP INDEX IDX_D8F0A91E9B875DF8 ON trick');
        $this->addSql('ALTER TABLE trick DROP trick_group_id');
        $this->addSql('ALTER TABLE trick_group ADD description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE trick_group_trick');
        $this->addSql('ALTER TABLE trick ADD trick_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91E9B875DF8 FOREIGN KEY (trick_group_id) REFERENCES trick_group (id)');
        $this->addSql('CREATE INDEX IDX_D8F0A91E9B875DF8 ON trick (trick_group_id)');
        $this->addSql('ALTER TABLE trick_group DROP description');
    }
}
