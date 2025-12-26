<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_article (article_source INT NOT NULL, article_target INT NOT NULL, PRIMARY KEY(article_source, article_target))');
        $this->addSql('CREATE INDEX IDX_EFE84AD1354DE8F3 ON article_article (article_source)');
        $this->addSql('CREATE INDEX IDX_EFE84AD12CA8B87C ON article_article (article_target)');
        $this->addSql('ALTER TABLE article_article ADD CONSTRAINT FK_EFE84AD1354DE8F3 FOREIGN KEY (article_source) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_article ADD CONSTRAINT FK_EFE84AD12CA8B87C FOREIGN KEY (article_target) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_article DROP CONSTRAINT FK_EFE84AD1354DE8F3');
        $this->addSql('ALTER TABLE article_article DROP CONSTRAINT FK_EFE84AD12CA8B87C');
        $this->addSql('DROP TABLE article_article');
    }
}
