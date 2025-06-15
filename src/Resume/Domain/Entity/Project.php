<?php

declare(strict_types=1);

namespace App\Resume\Domain\Entity;

use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Resume\Infrastructure\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * @package App\Resume\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'resume_project')]
class Project
{
    final public const string SET_USER_PROJECT = 'set.UserProject';

    use Blameable;
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
        'Project',
        'Project.id',

        self::SET_USER_PROJECT,
    ])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Groups([
        'Project',
        'Project.name',

        self::SET_USER_PROJECT,
    ])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([
        'Project',
        'Project.description',

        self::SET_USER_PROJECT,
    ])]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Media::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        'Project',
        'Project.medias',

        self::SET_USER_PROJECT,
    ])]
    private Collection $medias;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([
        'Project',
        'Project.gitLink',

        self::SET_USER_PROJECT,
    ])]
    private ?string $gitLink = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'projects')]
    private ?Reference $reference = null;

    #[ORM\ManyToMany(targetEntity: Skill::class, inversedBy: 'projects')]
    #[Groups([
        'Project',
        'Project.skills',

        self::SET_USER_PROJECT,
    ])]
    private Collection $skills;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->medias = new ArrayCollection();
        $this->skills = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): static
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setProject($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getProject() === $this) {
                $media->setProject(null);
            }
        }

        return $this;
    }

    public function getGitLink(): ?string
    {
        return $this->gitLink;
    }

    public function setGitLink(?string $gitLink): static
    {
        $this->gitLink = $gitLink;

        return $this;
    }

    public function getReference(): ?Reference
    {
        return $this->reference;
    }

    public function setReference(?Reference $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        $this->skills->removeElement($skill);

        return $this;
    }
}
