<?php

declare(strict_types=1);

namespace App\Resume\Domain\EntityListener;

use App\Resume\Domain\Entity\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Uuid;

/**
 * @package App\EntityListener
 */
class MediaListener
{
    private string $uploadDir;

    private string $uploadAbsoluteDir;

    public function __construct(string $uploadDir, string $uploadAbsoluteDir)
    {
        $this->uploadDir = $uploadDir;
        $this->uploadAbsoluteDir = $uploadAbsoluteDir;
    }

    public function prePersist(Media $media): void
    {
        $this->upload($media);
    }

    public function preUpdate(Media $media): void
    {
        $this->upload($media);
    }

    private function upload(Media $media): void
    {
        if ($media->getFile() instanceof UploadedFile) {
            $filename = sprintf('%s.%s', Uuid::V4_RANDOM, $media->getFile()->getClientOriginalExtension());
            $media->getFile()->move($this->uploadAbsoluteDir, $filename);
            $media->setPath(sprintf('%s/%s', $this->uploadDir, $filename));
        }
    }
}
