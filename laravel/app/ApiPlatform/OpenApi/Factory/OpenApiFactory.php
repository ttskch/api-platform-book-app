<?php

namespace App\ApiPlatform\OpenApi\Factory;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated)
    {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            assert(is_string($path));
            assert($pathItem instanceof Model\PathItem);

            foreach (Model\PathItem::$methods as $method) {
                $getter = sprintf('get%s', ucfirst(strtolower($method)));
                $wither = sprintf('with%s', ucfirst(strtolower($method)));

                $operation = $pathItem->{$getter}();
                assert($operation instanceof Model\Operation || $operation === null);

                if ($operation === null) {
                    continue;
                }

                // summary を description にコピー
                $operation = $operation->withDescription($operation->getSummary() ?? '');

                // PUT /api/articles/{id}/publication ではリクエストボディを削除
                if (strtolower($method) === 'put' && $path === '/api/articles/{id}/publication') {
                    $operation = $operation->withRequestBody();
                }

                $pathItem = $pathItem->{$wither}($operation);

                $openApi->getPaths()->addPath($path, $pathItem);
            }
        }

        return $openApi;
    }
}
