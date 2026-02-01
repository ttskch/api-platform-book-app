<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\DecoderInterface;

class MultipartDecoder implements DecoderInterface
{
    public const FORMAT = 'multipart';

    public function decode(string $data, string $format, array $context = []): array
    {
        // クライアント側でJSONエンコードされる場合が多いのでそれを考慮する
        $formData = array_map(
            fn (string $value) => json_decode($value, true) ?? $value,
            request()->request->all(),
        );

        $files = request()->allFiles();

        return array_merge($formData, $files);
    }

    public function supportsDecoding(string $format): bool
    {
        return $format === self::FORMAT;
    }
}
