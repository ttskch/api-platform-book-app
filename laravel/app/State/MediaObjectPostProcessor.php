<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Models\MediaObject;
use Illuminate\Support\Str;

final class MediaObjectPostProcessor implements ProcessorInterface
{
    public function __construct(private PersistProcessor $decorated)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MediaObject
    {
        if (!$data instanceof MediaObject) {
            throw new \InvalidArgumentException('このカスタムステートプロセッサーはMediaObjectリソースに対してのみ使用可能です。');
        }

        // ファイルをストレージに保存してファイルパスをモデルにセット
        if ($data->file !== null) {
            $filename = sprintf('%s.%s', Str::uuid(), $data->file->getClientOriginalExtension());
            $data->file_path = strval($data->file->storeAs('media', $filename, 'public'));
        }

        $data = $this->decorated->process($data, $operation, $uriVariables, $context);
        assert($data instanceof MediaObject);

        return $data;
    }
}
