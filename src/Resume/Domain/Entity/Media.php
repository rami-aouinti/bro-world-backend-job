<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Rami Aouinti <rami.aouinti@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Resume\Domain\Entity;

use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Resume\Domain\Repository\MediaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;
use App\Resume\Domain\EntityListener\MediaListener;

/**
 * @package App\Resume\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\EntityListeners([MediaListener::class])]
#[ORM\Table(name: 'resume_media')]
class Media
{
    final public const string SET_USER_MEDIA = 'set.UserMedia';

    use Timestampable;
    use Uuid;

    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidBinaryOrderedTimeType::NAME,
        unique: true,
        nullable: false,
    )]
    #[Groups([
        'Media',
        'Media.id',

        self::SET_USER_MEDIA,
    ])]
    private UuidInterface $id;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups([
        'Media',
        'Media.path',

        self::SET_USER_MEDIA,
    ])]
    private ?string $path = null;

    /**
     * @Assert\Image
     */
    private ?UploadedFile $file = null;

    #[ORM\ManyToOne(targetEntity: Reference::class, inversedBy: 'medias')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Assert\Count(min: 1, minMessage: 'add image please')]
    private ?Reference $reference;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    private ?Project $project = null;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getReference(): ?Reference
    {
        return $this->reference;
    }

    public function setReference(?Reference $reference): void
    {
        $this->reference = $reference;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }
}
