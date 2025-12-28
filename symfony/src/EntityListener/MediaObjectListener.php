<?php

namespace App\EntityListener;

use App\Entity\MediaObject;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Storage\StorageInterface;

#[AsEntityListener(entity: MediaObject::class)]
class MediaObjectListener
{
    public function __construct(
        private StorageInterface $storage,
        private RequestStack $requestStack,
    ) {
    }

    public function postLoad(MediaObject $mediaObject, PostLoadEventArgs $event): void
    {
        $path = $this->storage->resolveUri($mediaObject, 'file');
        $baseUrl = $this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost();
        $mediaObject->contentUrl = $baseUrl.$path;
    }
}
