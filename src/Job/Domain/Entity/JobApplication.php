<?php

namespace App\Job\Domain\Entity;

use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Job\Domain\Enum\ApplicationStatus;
use App\Job\Infrastructure\Repository\JobApplicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

#[ORM\Entity(repositoryClass: JobApplicationRepository::class)]
class JobApplication
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
        'Application',
        'Application.id',
    ])]
    private UuidInterface $id;

    #[Assert\NotBlank]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        'Application',
        'Application.job',
    ])]
    private ?Job $job = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        'Application',
        'Application.applicant',
    ])]
    private ?Applicant $applicant = null;

    #[ORM\Column(type: 'string', enumType: ApplicationStatus::class)]
    #[Groups(['Application', 'Application.status'])]
    private ApplicationStatus $status = ApplicationStatus::Request;

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

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): self
    {
        $this->job = $job;

        return $this;
    }

    public function getApplicant(): ?Applicant
    {
        return $this->applicant;
    }

    public function setApplicant(?Applicant $applicant): self
    {
        $this->applicant = $applicant;

        return $this;
    }

    public function getStatus(): ApplicationStatus
    {
        return $this->status;
    }

    public function setStatus(ApplicationStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function toArray(): array
    {
        return [
            "id"=>$this->getId(),
            "applicant"=>$this->getApplicant()?->toArray(),
            "job"=>$this->getJob()?->toArray(),
            "status" => $this->getStatus()->value,
            "createdAt"=>$this->getCreatedAt(),
            "updatedAt"=>$this->getUpdatedAt()
        ];
    }
}
