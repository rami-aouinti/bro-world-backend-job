<?php

declare(strict_types=1);

namespace App\Resume\Domain\Entity;

use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Resume\Infrastructure\Repository\LanguageRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * @package App\Resume\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: LanguageRepository::class)]
#[ORM\Table(name: 'resume_language')]
class Language
{
    final public const string SET_USER_LANGUAGE = 'set.UserLanguage';

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
        'Language',
        'Language.id',

        self::SET_USER_LANGUAGE,
    ])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Groups([
        'Language',
        'Language.name',

        self::SET_USER_LANGUAGE,
    ])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups([
        'Language',
        'Language.level',

        self::SET_USER_LANGUAGE,
    ])]
    private ?int $level = null;

    #[ORM\Column(type: 'uuid')]
    #[Groups([
        'Language',
        'Language.user',
    ])]
    private UuidInterface $user;

    #[ORM\Column(length: 255)]
    #[Groups([
        'Language',
        'Language.flag',

        self::SET_USER_LANGUAGE,
    ])]
    private ?string $flag = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getUser(): UuidInterface
    {
        return $this->user;
    }

    public function setUser(UuidInterface $user): void
    {
        $this->user = $user;
    }

    public function getFlag(): ?string
    {
        return $this->flag;
    }

    public function setFlag(string $flag): static
    {
        $this->flag = $flag;

        return $this;
    }
}
