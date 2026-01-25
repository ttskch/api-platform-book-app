<?php

namespace App\ApiPlatform\Hydra\JsonSchema;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Metadata\Operation;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: 'api_platform.hydra.json_schema.schema_factory')]
class SchemaFactory implements SchemaFactoryInterface
{
    public function __construct(private SchemaFactoryInterface $decorated)
    {
    }

    public function buildSchema(string $className, string $format = 'json', string $type = Schema::TYPE_OUTPUT, ?Operation $operation = null, ?Schema $schema = null, ?array $serializerContext = null, bool $forceCollection = false): Schema
    {
        $schema = $this->decorated->buildSchema($className, $format, $type, $operation, $schema, $serializerContext, $forceCollection);

        $definitions = $schema->getDefinitions();
        $key = $schema->getRootDefinitionKey() ?? $schema->getItemsDefinitionKey();

        // Article.jsonld スキーマにおいて published プロパティを requiredに
        if ($key === 'Article.jsonld') {
            $definitions[$key]['required'][] = 'published';
        }

        return $schema;
    }
}
