<?php

namespace App\Serializer;

use Illuminate\Http\UploadedFile;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UploadedFileDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return $data;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UploadedFile;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            UploadedFile::class => true,
        ];
    }
}
