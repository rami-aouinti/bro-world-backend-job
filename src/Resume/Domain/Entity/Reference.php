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
use App\Resume\Infrastructure\Repository\ReferenceRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * @package App\Resume\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: ReferenceRepository::class)]
#[ORM\Table(name: 'resume_reference')]
class Reference
{
    final public const string SET_USER_REFERENCE = 'set.UserReference';

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
        'Reference',
        'Reference.id',

        self::SET_USER_REFERENCE,
    ])]
    private UuidInterface $id;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups([
        'Reference',
        'Reference.title',

        self::SET_USER_REFERENCE,
    ])]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups([
        'Reference',
        'Reference.company',

        self::SET_USER_REFERENCE,
    ])]
    private ?string $company = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups([
        'Reference',
        'Reference.description',

        self::SET_USER_REFERENCE,
    ])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    #[Groups([
        'Reference',
        'Reference.startedAt',

        self::SET_USER_REFERENCE,
    ])]
    private ?DateTimeInterface $startedAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups([
        'Reference',
        'Reference.endedAt',

        self::SET_USER_REFERENCE,
    ])]
    private ?DateTimeInterface $endedAt = null;

    #[ORM\OneToMany(mappedBy: 'reference', targetEntity: Media::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(min: 1, minMessage: 'add image please')]
    private Collection $medias;

    #[ORM\Column(type: 'uuid')]
    #[Groups([
        'Reference',
        'Reference.user',
    ])]
    private UuidInterface $user;

    #[ORM\OneToMany(mappedBy: 'reference', targetEntity: Project::class)]
    #[Groups([
        'Reference',
        'Reference.projects',

        self::SET_USER_REFERENCE,
    ])]
    private Collection $projects;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->medias = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStartedAt(): ?DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?DateTimeInterface $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getEndedAt(): ?DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?DateTimeInterface $endedAt): void
    {
        $this->endedAt = $endedAt;
    }

    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): void
    {
        if (!$this->medias->contains($media)) {
            $media->setReference($this);
            $this->medias->add($media);
        }
    }

    public function removeMedia(Media $media): void
    {
        if ($this->medias->contains($media)) {
            $media->setReference(null);
            $this->medias->removeElement($media);
        }
    }

    public function getUser(): UuidInterface
    {
        return $this->user;
    }

    public function setUser(UuidInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setReference($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getReference() === $this) {
                $project->setReference(null);
            }
        }

        return $this;
    }
}
