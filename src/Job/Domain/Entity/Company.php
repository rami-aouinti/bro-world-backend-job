<?php

namespace App\Job\Domain\Entity;

use Bro\WorldCoreBundle\Domain\Entity\Traits\Timestampable;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Uuid;
use App\Job\Infrastructure\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
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
        'Company',
        'Job',
        'Company.id',
        'Application',
    ])]
    private UuidInterface $id;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length( min: 5, max: 255)]
    #[Groups([
        'Company',
        'Job',
        'Company.name',
        'Application',
    ])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([
        'Company',
        'Job',
        'Company.description',
        'Application',
    ])]
    private ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Length( min: 5, max: 255)]
    #[ORM\Column(length: 255)]
    #[Groups([
        'Company',
        'Job',
        'Application',
        'Company.location',
    ])]
    private ?string $location = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length( min: 5, max: 255)]
    #[ORM\Column(length: 255)]
    #[Groups([
        'Company',
        'Job',
        'Company.contactEmail',
        'Application',
    ])]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['Company', 'Job', 'Company.logo', 'Application'])]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['Company', 'Job','Company.siteUrl',  'Application'])]
    private ?string $siteUrl = null;

    /**
     * @var Collection<int, CompanyMedia>
     */
    #[ORM\OneToMany(
        mappedBy: 'company',
        targetEntity: CompanyMedia::class,
        cascade: ['persist'],
        orphanRemoval: true)
    ]
    #[Groups(['Company', 'Job', 'Application'])]
    private Collection $medias;

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
        $this->medias = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getSiteUrl(): ?string
    {
        return $this->siteUrl;
    }

    public function setSiteUrl(?string $siteUrl): self
    {
        $this->siteUrl = $siteUrl;

        return $this;
    }

    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function setMedias(Collection $medias): void
    {
        $this->medias = $medias;
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
            "description"=>$this->getDescription(),
            "location"=>$this->getLocation(),
            "contactEmail"=>$this->getContactEmail(),
            "logo"=>$this->getLogo(),
            "siteUrl"=>$this->getSiteUrl(),
            "medias"=>$this->getMedias()->map(fn(CompanyMedia $media) => $media->toArray())->toArray(),
            "user"=>$this->getUser(),
            "createdAt"=>$this->getCreatedAt(),
            "updatedAt"=>$this->getUpdatedAt()
        ];
    }
}
