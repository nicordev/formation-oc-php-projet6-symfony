<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190501124011 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, content VARCHAR(1000) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME DEFAULT NULL, is_seen_by_moderator TINYINT(1) NOT NULL, INDEX IDX_9474526CF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE image ADD member_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F7597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045F7597D3FE ON image (member_id)');
        $this->addSql('ALTER TABLE member DROP picture');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE comment');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F7597D3FE');
        $this->addSql('DROP INDEX UNIQ_C53D045F7597D3FE ON image');
        $this->addSql('ALTER TABLE image DROP member_id');
        $this->addSql('ALTER TABLE member ADD picture VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
