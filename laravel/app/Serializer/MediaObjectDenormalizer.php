<?php

namespace App\Serializer;

use App\Models\MediaObject;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MediaObjectDenormalizer implements DenormalizerInterface
{
    public function __construct(private iterable $normalizers)
    {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $mediaObject = null;

        // ファイルがアップロードされていたら退避
        $file = null;
        if (is_array($data) && ($data['file'] ?? null) instanceof UploadedFile) {
            $file = $data['file'];
            unset($data['file']);
        }

        // まずは最初に適合するデノーマライザーでデノーマライズする
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof DenormalizerInterface && $normalizer->supportsDenormalization($data, $type, $format, $context)) {
                $mediaObject = $normalizer->denormalize($data, $type, $format, $context);
                break;
            }
        }

        // アップロードされていたファイルをモデルにセット
        if ($mediaObject instanceof MediaObject && $file !== null) {
            $mediaObject->file = $file;
        }

        return $mediaObject;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MediaObject::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MediaObject::class => true,
        ];
    }
}
