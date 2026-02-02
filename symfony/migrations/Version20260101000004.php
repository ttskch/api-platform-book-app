<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        // 一旦NULLを許容する設定でカラムを追加
        $this->addSql('ALTER TABLE article ADD date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        // すべての行の date, created_at, updated_at カラムに 2026-01-01 00:00:00 から始まる連日をセット
        $this->addSql(<<<SQL
UPDATE article a
SET date = '2026-01-01'::date + row.num * INTERVAL '1 day'
FROM (SELECT id, ROW_NUMBER() OVER (ORDER BY id) - 1 AS num FROM article) row
WHERE a.id = row.id;
SQL);
        $this->addSql('UPDATE article SET created_at = date, updated_at = date');

        // 改めてNULLを許容しない設定に変更
        $this->addSql('ALTER TABLE article ALTER date SET NOT NULL');
        $this->addSql('ALTER TABLE article ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE article ALTER updated_at SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP date');
        $this->addSql('ALTER TABLE article DROP created_at');
        $this->addSql('ALTER TABLE article DROP updated_at');
    }
}
