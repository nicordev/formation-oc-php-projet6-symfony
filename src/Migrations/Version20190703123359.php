<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190703123359 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trick ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE trick_trick_group DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE trick_trick_group ADD PRIMARY KEY (trick_id, trick_group_id)');
        $this->addSql('ALTER TABLE trick_trick_group RENAME INDEX idx_6ebe510b281be2e TO IDX_28D82313B281BE2E');
        $this->addSql('ALTER TABLE trick_trick_group RENAME INDEX idx_6ebe5109b875df8 TO IDX_28D823139B875DF8');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trick DROP slug');
        $this->addSql('ALTER TABLE trick_trick_group DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE trick_trick_group ADD PRIMARY KEY (trick_group_id, trick_id)');
        $this->addSql('ALTER TABLE trick_trick_group RENAME INDEX idx_28d823139b875df8 TO IDX_6EBE5109B875DF8');
        $this->addSql('ALTER TABLE trick_trick_group RENAME INDEX idx_28d82313b281be2e TO IDX_6EBE510B281BE2E');
    }
}
