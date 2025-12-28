<?php

namespace App\Serializer;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class MultipartDecoder implements DecoderInterface
{
    public const string FORMAT = 'multipart';

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function decode(string $data, string $format, array $context = []): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return [];
        }

        // クライアント側でJSONエンコードされる場合が多いのでそれを考慮する
        $formData = array_map(
            fn (string $value) => json_decode($value, true) ?? $value,
            $request->request->all(),
        );

        $files = $request->files->all();

        return array_merge($formData, $files);
    }

    public function supportsDecoding(string $format): bool
    {
        return $format === self::FORMAT;
    }
}
