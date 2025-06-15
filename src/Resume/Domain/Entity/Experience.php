<?php

declare(strict_types=1);

namespace App\Resume\Domain\Entity;

use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Resume\Infrastructure\Repository\ExperienceRepository;
use DateTimeInterface;
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
#[ORM\Entity(repositoryClass: ExperienceRepository::class)]
#[ORM\Table(name: 'resume_experience')]
class Experience
{
    final public const string SET_USER_EXPERIENCE = 'set.UserExperience';

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
        'Experience',
        'Experience.id',

        self::SET_USER_EXPERIENCE,
    ])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Groups([
        'Experience',
        'Experience.title',

        self::SET_USER_EXPERIENCE,
    ])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([
        'Experience',
        'Experience.description',

        self::SET_USER_EXPERIENCE,
    ])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        'Experience',
        'Experience.company',

        self::SET_USER_EXPERIENCE,
    ])]
    private ?string $company = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    #[Groups([
        'Experience',
        'Experience.startedAt',

        self::SET_USER_EXPERIENCE,
    ])]
    private ?DateTimeInterface $startedAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups([
        'Experience',
        'Experience.endedAt',

        self::SET_USER_EXPERIENCE,
    ])]
    private ?DateTimeInterface $endedAt = null;

    #[ORM\Column(type: 'uuid')]
    #[Groups([
        'Experience',
        'Experience.user',
    ])]
    private UuidInterface $user;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;

        return $this;
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

    public function getUser(): UuidInterface
    {
        return $this->user;
    }

    public function setUser(UuidInterface $user): void
    {
        $this->user = $user;
    }
}
