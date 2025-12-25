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

        // description に "#required-on-read" が含まれるプロパティを、output スキーマにおいてのみ required に
        if ($key !== null && $type === Schema::TYPE_OUTPUT) {
            foreach ($definitions[$key]['allOf'][1]['properties'] ?? [] as $name => $property) {
                $description = $property['description'] ?? '';
                if (str_contains($description, '#required-on-read')) {
                    $definitions[$key]['allOf'][1]['required'][] = $name;
                }
                $property['description'] = preg_replace('/\s*#required-on-read\s*/', '', $description);
            }
        }

        return $schema;
    }
}
