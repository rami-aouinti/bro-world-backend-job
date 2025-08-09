<?php

declare(strict_types=1);

namespace App\Resume\Domain\Entity;

use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Resume\Infrastructure\Repository\TemplateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * Template entity representing a CV/Resume template card for the frontend.
 *
 * Example serialized JSON (group: "Template"):
 * {
 *   "id": "cv-2025",
 *   "title": "Lebenslauf 2025",
 *   "subtitle": "Moderne, sauber",
 *   "category": "Kreativ",
 *   "badge": "TOP",
 *   "previewImg": "https://picsum.photos/seed/cv2025/800/1130",
 *   "pdfUrl": "/samples/cv-2025.pdf",
 *   "pages": 2,
 *   "tags": ["Modern", "ATS-friendly"]
 * }
 */
#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\Table(name: 'resume_template')]
#[UniqueEntity(fields: ['slug'], message: 'This template id/slug is already used.')]
class Template
{
    final public const string SET_TEMPLATE = 'set.Template';

    use Timestampable;
    use Uuid;

    /**
     * Internal primary key (UUID binary ordered time) — not exposed in API groups
     */
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: UuidBinaryOrderedTimeType::NAME, unique: true, nullable: false)]
    private UuidInterface $id;

    /**
     * Public identifier used by the frontend (e.g. "cv-2025").
     * Serialized as `id`.
     */
    #[ORM\Column(name: 'slug', length: 100, unique: true)]
    #[Groups(['Template', self::SET_TEMPLATE])]
    #[SerializedName('id')]
    #[Assert\NotBlank]
    private string $slug;

    #[ORM\Column(length: 255)]
    #[Groups(['Template', self::SET_TEMPLATE])]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\Column(length: 255)]
    #[Groups(['Template', self::SET_TEMPLATE])]
    #[Assert\NotBlank]
    private string $subtitle;

    #[ORM\Column(length: 100)]
    #[Groups(['Template', self::SET_TEMPLATE])]
    #[Assert\NotBlank]
    private string $category;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['Template', self::SET_TEMPLATE])]
    private ?string $badge = null;

    #[ORM\Column(length: 2048)]
    #[Groups(['Template', self::SET_TEMPLATE])]
    #[Assert\NotBlank]
    #[Assert\Url(relativeProtocol: true, allowRelativePath: true)]
    private string $previewImg;

    #[ORM\Column(length: 2048)]
    #[Groups(['Template', self::SET_TEMPLATE])]
    #[Assert\NotBlank]
    #[Assert\Url(relativeProtocol: true, allowRelativePath: true)]
    private string $pdfUrl;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['Template', self::SET_TEMPLATE])]
    #[Assert\Positive]
    private int $pages;

    /** @var string[] */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['Template', self::SET_TEMPLATE])]
    #[Assert\All([new Assert\Type('string')])]
    private array $tags = [];

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    // --- Internal UUID accessor (not in serialization groups) ---
    public function getUuid(): string
    {
        return $this->id->toString();
    }

    // --- Public API fields ---
    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): self
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function setBadge(?string $badge): self
    {
        $this->badge = $badge;
        return $this;
    }

    public function getPreviewImg(): string
    {
        return $this->previewImg;
    }

    public function setPreviewImg(string $previewImg): self
    {
        $this->previewImg = $previewImg;
        return $this;
    }

    public function getPdfUrl(): string
    {
        return $this->pdfUrl;
    }

    public function setPdfUrl(string $pdfUrl): self
    {
        $this->pdfUrl = $pdfUrl;
        return $this;
    }

    public function getPages(): int
    {
        return $this->pages;
    }

    public function setPages(int $pages): self
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     */
    public function setTags(array $tags): self
    {
        $this->tags = array_values($tags);
        return $this;
    }

    public function addTag(string $tag): self
    {
        if (!in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }
        return $this;
    }

    public function removeTag(string $tag): self
    {
        $this->tags = array_values(array_filter($this->tags, static fn(string $t): bool => $t !== $tag));
        return $this;
    }
}
