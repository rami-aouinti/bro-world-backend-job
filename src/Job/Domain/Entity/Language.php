<?php

namespace App\Job\Domain\Entity;

use App\Job\Domain\Enum\LanguageLevel;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity()]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['Job'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['Job'])]
    private string $name;

    #[ORM\Column(enumType: LanguageLevel::class)]
    #[Groups(['Job'])]
    private LanguageLevel $level;

    #[ORM\ManyToOne(inversedBy: 'languages')]
    private ?Job $job = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getLevel(): LanguageLevel
    {
        return $this->level;
    }

    public function setLevel(LanguageLevel $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): self
    {
        $this->job = $job;
        return $this;
    }
}
