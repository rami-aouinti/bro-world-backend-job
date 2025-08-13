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

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\Table(name: 'resume_template')]
#[UniqueEntity(fields: ['key'], message: 'Preset key already exists.')]
class Template
{
    use Timestampable;
    use Uuid;

    #[ORM\Id]
    #[ORM\Column(type: UuidBinaryOrderedTimeType::NAME, unique: true, nullable: false)]
    private UuidInterface $id;

    #[ORM\Column(name: 'preset_key', length: 100, unique: true)]
    #[Groups(['cv:read'])]
    #[Assert\NotBlank]
    #[SerializedName('key')] // <-- on garde "key" en sortie JSON
    private string $key;

    #[ORM\Column(length: 255)]
    #[Groups(['cv:read'])]
    #[Assert\NotBlank]
    private string $label;

    /** ⚠️ "default" est mot réservé; on stocke dans isDefault mais on sérialise sous "default" */
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    #[Groups(['cv:read'])]
    #[SerializedName('default')]
    private ?bool $isDefault = null;

    #[ORM\Column(length: 100)]
    #[Groups(['cv:read'])]
    #[Assert\NotBlank]
    private string $fontFamily;

    #[ORM\Column(length: 20)]
    #[Groups(['cv:read'])]
    #[Assert\NotBlank]
    private string $baseSize;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Groups(['cv:read'])]
    private ?string $previewImg = null;

    /** { primary, accent, paper, text } */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['cv:read'])]
    private array $palette = [];

    /** { show, position, widthMm, heightMm, rounded } */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['cv:read'])]
    private array $photo = [];

    /** { enabled, elevation, color, custom? } */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['cv:read'])]
    private ?array $photoShadow = null;

    /** 'stylish' | 'sidebar-left' | 'sidebar-right' | 'stacked' | 'photo-left' */
    #[ORM\Column(length: 50)]
    #[Groups(['cv:read'])]
    #[Assert\NotBlank]
    private string $layout;

    /** { enabled?, widthMm?, background, text?, borderColor? } */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['cv:read'])]
    private ?array $sidebar = null;

    /** { type, anchor?, color?, color2?, sizeMm?, offsetMmX?, offsetMmY?, rotateDeg?, enabled? } */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['cv:read'])]
    private ?array $corner = null;

    /** { show, side?, color?, widthMm?, offsetMm? } */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['cv:read'])]
    private ?array $vbar = null;

    /** { chipVariant, chipColor?, chipDensity?, editable?, draggable? } */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['cv:read'])]
    private ?array $skills = null;

    /** { variant, maxLevel?, showNote?, sizePx?, accent? } */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['cv:read'])]
    private ?array $languages = null;

    // --- Nouveaux champs plats demandés
    #[ORM\Column(length: 50)]
    #[Groups(['cv:read'])]
    #[Assert\NotBlank]
    private string $category; // 'Creative' | 'Classic' | 'Premium' ...

    #[ORM\Column(length: 50)]
    #[Groups(['cv:read'])]
    #[Assert\NotBlank]
    private string $template; // 'CV' | 'Cover'

    #[ORM\Column(length: 2048)]
    #[Groups(['cv:read'])]
    #[Assert\NotBlank]
    private string $src;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['cv:read'])]
    private int $downloads = 0;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['cv:read'])]
    private int $views = 0;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
    }

    // --- Getters/Setters (seuls nécessaires montrés ici) ---
    public function getKey(): string { return $this->key; }
    public function setKey(string $key): self { $this->key = $key; return $this; }

    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): self { $this->label = $label; return $this; }

    public function isDefault(): ?bool { return $this->isDefault; }
    public function setIsDefault(?bool $isDefault): self { $this->isDefault = $isDefault; return $this; }

    public function getFontFamily(): string { return $this->fontFamily; }
    public function setFontFamily(string $fontFamily): self { $this->fontFamily = $fontFamily; return $this; }

    public function getBaseSize(): string { return $this->baseSize; }
    public function setBaseSize(string $baseSize): self { $this->baseSize = $baseSize; return $this; }

    public function getPreviewImg(): ?string { return $this->previewImg; }
    public function setPreviewImg(?string $previewImg): self { $this->previewImg = $previewImg; return $this; }

    public function getPalette(): array { return $this->palette; }
    public function setPalette(array $palette): self { $this->palette = $palette; return $this; }

    public function getPhoto(): array { return $this->photo; }
    public function setPhoto(array $photo): self { $this->photo = $photo; return $this; }

    public function getPhotoShadow(): ?array { return $this->photoShadow; }
    public function setPhotoShadow(?array $photoShadow): self { $this->photoShadow = $photoShadow; return $this; }

    public function getLayout(): string { return $this->layout; }
    public function setLayout(string $layout): self { $this->layout = $layout; return $this; }

    public function getSidebar(): ?array { return $this->sidebar; }
    public function setSidebar(?array $sidebar): self { $this->sidebar = $sidebar; return $this; }

    public function getCorner(): ?array { return $this->corner; }
    public function setCorner(?array $corner): self { $this->corner = $corner; return $this; }

    public function getVbar(): ?array { return $this->vbar; }
    public function setVbar(?array $vbar): self { $this->vbar = $vbar; return $this; }

    public function getSkills(): ?array { return $this->skills; }
    public function setSkills(?array $skills): self { $this->skills = $skills; return $this; }

    public function getLanguages(): ?array { return $this->languages; }
    public function setLanguages(?array $languages): self { $this->languages = $languages; return $this; }

    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }

    public function getTemplate(): string { return $this->template; }
    public function setTemplate(string $template): self { $this->template = $template; return $this; }

    public function getSrc(): string { return $this->src; }
    public function setSrc(string $src): self { $this->src = $src; return $this; }

    public function getDownloads(): int { return $this->downloads; }
    public function setDownloads(int $downloads): self { $this->downloads = $downloads; return $this; }
    public function incDownloads(): void { ++$this->downloads; }

    public function getViews(): int { return $this->views; }
    public function setViews(int $views): self { $this->views = $views; return $this; }
    public function incViews(): void { ++$this->views; }
}
