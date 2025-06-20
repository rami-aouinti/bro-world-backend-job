<?php

namespace App\Job\Domain\Entity;

use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Job\Domain\Enum\ContractType;
use App\Job\Domain\Enum\WorkType;
use App\Job\Infrastructure\Repository\JobRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
        'Application',
    ])]
    private UuidInterface $id;

    #[Assert\NotBlank]
    #[Assert\Length( min: 5, max: 255)]
    #[ORM\Column(length: 255)]
    #[Groups([
        'Job',
        'Job.title',
        'Application',
    ])]
    private ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Length( min: 5)]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups([
        'Job',
        'Job.description',
        'Application',
    ])]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['Job', 'Job.requiredSkills', 'Application'])]
    private array $requiredSkills = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['Job', 'Job.experience', 'Application'])]
    private ?string $experience = null;

    #[ORM\Column(nullable: true, enumType: WorkType::class)]
    #[Groups(['Job', 'Application'])]
    private ?WorkType $workType = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['Job', 'Application'])]
    private ?string $workLocation = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['Job', 'Application'])]
    private ?string $salaryRange = null;

    #[ORM\Column(nullable: true, enumType: ContractType::class)]
    #[Groups(['Job', 'Application'])]
    private ?ContractType $contractType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['Job', 'Application'])]
    private ?string $requirements = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['Job', 'Application'])]
    private ?string $benefits = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['Job', 'Job.company', 'Application'])]
    private ?Company $company = null;

    #[ORM\Column(type: 'uuid')]
    #[Groups(['Job', 'Job.user'])]
    private UuidInterface $user;

    #[ORM\OneToMany(mappedBy: 'job', targetEntity: Language::class, cascade: ['persist', 'remove'])]
    #[Groups(['Job', 'Job.languages', 'Application'])]
    private Collection $languages;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->languages = new ArrayCollection();
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

    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): self
    {
        if (!$this->languages->contains($language)) {
            $this->languages[] = $language;
            $language->setJob($this);
        }

        return $this;
    }

    public function removeLanguage(Language $language): self
    {
        if ($this->languages->removeElement($language)) {
            if ($language->getJob() === $this) {
                $language->setJob(null);
            }
        }

        return $this;
    }



    public function getRequiredSkills(): ?array
    {
        return $this->requiredSkills;
    }

    public function setRequiredSkills(array $requiredSkills): self
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

    public function getWorkType(): ?WorkType
    {
        return $this->workType;
    }

    public function setWorkType(?WorkType $workType): self
    {
        $this->workType = $workType;

        return $this;
    }

    public function getWorkLocation(): ?string
    {
        return $this->workLocation;
    }

    public function setWorkLocation(?string $workLocation): void
    {
        $this->workLocation = $workLocation;
    }

    public function getSalaryRange(): ?string
    {
        return $this->salaryRange;
    }

    public function setSalaryRange(?string $salaryRange): void
    {
        $this->salaryRange = $salaryRange;
    }

    public function getContractType(): ?ContractType
    {
        return $this->contractType;
    }

    public function setContractType(?ContractType $contractType): self
    {
        $this->contractType = $contractType;

        return $this;
    }

    public function getRequirements(): ?string
    {
        return $this->requirements;
    }

    public function setRequirements(?string $requirements): void
    {
        $this->requirements = $requirements;
    }

    public function getBenefits(): ?string
    {
        return $this->benefits;
    }

    public function setBenefits(?string $benefits): void
    {
        $this->benefits = $benefits;
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

    public function getUser(): UuidInterface
    {
        return $this->user;
    }

    public function setUser(UuidInterface $user): void
    {
        $this->user = $user;
    }

    public function toArray(): array
    {
        return [
            "id"=>$this->getId(),
            "title"=>$this->getTitle(),
            "description"=>$this->getDescription(),
            "requiredSkills"=>$this->getRequiredSkills(),
            "experience"=>$this->getExperience(),
            "workType"=>$this->getWorkType(),
            "workLocation"=>$this->getWorkLocation(),
            "salaryRange"=>$this->getSalaryRange(),
            "languages"=>$this->getLanguages(),
            "contractType"=>$this->getContractType(),
            "requirements"=>$this->getRequirements(),
            "benefits"=>$this->getBenefits(),
            "company"=>$this->getCompany()?->toArray(),
            "user"=>$this->getUser(),
            "createdAt"=>$this->getCreatedAt(),
            "updatedAt"=>$this->getUpdatedAt()
        ];
    }
}
