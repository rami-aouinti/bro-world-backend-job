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

use Bro\WorldCoreBundle\Domain\Entity\Traits\Timestampable;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Uuid;
use App\Resume\Infrastructure\Repository\SkillRepository;
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
#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Table(name: 'resume_skill')]
class Skill
{
    final public const string SET_USER_SKILL = 'set.UserSkill';

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
        'Skill',
        'Skill.id',

        self::SET_USER_SKILL,
    ])]
    private UuidInterface $id;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups([
        'Skill',
        'Skill.name',

        self::SET_USER_SKILL,
    ])]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups([
        'Skill',
        'Skill.type',

        self::SET_USER_SKILL,
    ])]
    private ?string $type = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank]
    #[Assert\Range(
        min: 1,
        max: 10,
    )]
    #[Groups([
        'Skill',
        'Skill.level',

        self::SET_USER_SKILL,
    ])]
    private ?int $level = null;

    #[ORM\Column(type: 'uuid')]
    #[Groups([
        'Skill',
        'Skill.user',
    ])]
    private UuidInterface $user;

    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'skills')]
    private Collection $projects;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->projects = new ArrayCollection();
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

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): static
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
            $project->addSkill($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            $project->removeSkill($this);
        }

        return $this;
    }
}
