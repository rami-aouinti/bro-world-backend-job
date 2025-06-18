<?php

namespace App\Job\Domain\Entity;

use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Job\Infrastructure\Repository\ApplicantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

#[ORM\Entity(repositoryClass: ApplicantRepository::class)]
class Applicant
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
        'Applicant',
        'Applicant.id',
        'Application',
    ])]
    private UuidInterface $id;

    #[Assert\NotBlank]
    #[Assert\Length( min: 5, max: 255)]
    #[ORM\Column(length: 255)]
    #[Groups([
        'Applicant',
        'Applicant.name',
        'Application',
    ])]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length( min: 5, max: 255)]
    #[ORM\Column(length: 255)]
    #[Groups([
        'Applicant',
        'Applicant.contactEmail',
        'Application',
    ])]
    private ?string $contactEmail = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([
        'Applicant',
        'Applicant.jobPreferences',
        'Application',
    ])]
    private ?string $jobPreferences = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups([
        'Applicant',
        'Applicant.resume',
        'Application',
    ])]
    private ?string $resume = null;

    #[ORM\Column(type: 'uuid')]
    #[Groups([
        'Job',
        'Job.user',
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getJobPreferences(): ?string
    {
        return $this->jobPreferences;
    }

    public function setJobPreferences(?string $jobPreferences): self
    {
        $this->jobPreferences = $jobPreferences;

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(?string $resume): void
    {
        $this->resume = $resume;
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
            "name"=>$this->getName(),
            "contactEmail"=>$this->getContactEmail(),
            "jobPreferences"=>$this->getJobPreferences(),
            "resume"=>$this->getResume(),
            "user"=>$this->getUser(),
            "createdAt"=>$this->getCreatedAt(),
            "updatedAt"=>$this->getUpdatedAt()
        ];
    }
}
