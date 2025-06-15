<?php

namespace App\Job\Domain\Entity;

use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Job\Infrastructure\Repository\JobRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

#[ORM\Entity(repositoryClass: JobRepository::class)]
class Job
{
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
        'Job',
        'Job.id',
    ])]
    private UuidInterface $id;

    #[Assert\NotBlank]
    #[Assert\Length( min: 5, max: 255)]
    #[ORM\Column(length: 255)]
    #[Groups([
        'Job',
        'Job.title',
    ])]
    private ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Length( min: 5)]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups([
        'Job',
        'Job.description',
    ])]
    private ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Length( min: 5)]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups([
        'Job',
        'Job.requiredSkills',
    ])]
    private ?string $requiredSkills = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([
        'Job',
        'Job.experience',
    ])]
    private ?string $experience = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        'Job',
        'Job.company',
    ])]
    private ?Company $company = null;

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

    public function __toString(): string
    {
        return $this->getTitle();
    }



    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRequiredSkills(): ?string
    {
        return $this->requiredSkills;
    }

    public function setRequiredSkills(string $requiredSkills): self
    {
        $this->requiredSkills = $requiredSkills;

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function toArray(): array
    {
        return [
            "id"=>$this->getId(),
            "title"=>$this->getTitle(),
            "description"=>$this->getDescription(),
            "requiredSkills"=>$this->getRequiredSkills(),
            "experience"=>$this->getExperience(),
            "company"=>$this->getCompany()?->toArray(),
            "createdAt"=>$this->getCreatedAt(),
            "updatedAt"=>$this->getUpdatedAt()
        ];
    }
}
