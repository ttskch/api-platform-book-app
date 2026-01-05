<?php

namespace App\Tests\Functional\Traits;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use PHPUnit\Framework\Attributes\Before;

trait ResetSequenceTrait
{
    private const array SEQUENCES = [
        'article_id_seq',
        'comment_id_seq',
        'media_object_id_seq',
        'user_id_seq',
    ];

    #[Before]
    protected function resetSequence(): void
    {
        $connection = static::getContainer()->get('database_connection');

        if (!$connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            return;
        }

        foreach (self::SEQUENCES as $sequence) {
            $connection->executeQuery(
                sprintf("SELECT setval('%s', 1, false)", $sequence),
            );
        }
    }
}
