<?php

namespace App\Tests\Fake;

use App\Entity\MediaObject;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Storage\StorageInterface;

#[AsDecorator(decorates: 'vich_uploader.storage')]
class FakeStorage implements StorageInterface
{
    public function upload(object $obj, PropertyMapping $mapping): void
    {
        /**
         * ここで与えたファイル名が、実際に {@see MediaObject::$filePath} の値としてデータベースに保存される
         * これを省略してしまうと機能テスト実行時にデータベースレイヤーの NOT NULL 制約違反でエラーとなってしまう
         */
        $mapping->setFileName($obj, 'dummy');
    }

    public function remove(object $obj, PropertyMapping $mapping): ?bool
    {
        return null;
    }

    public function resolvePath(object|array $obj, ?string $fieldName = null, ?string $className = null, ?bool $relative = false): ?string
    {
        return null;
    }

    public function resolveUri(object|array $obj, ?string $fieldName = null, ?string $className = null): ?string
    {
        return '/fake-uri';
    }

    public function resolveStream(object|array $obj, ?string $fieldName = null, ?string $className = null)
    {
        return null;
    }
}
