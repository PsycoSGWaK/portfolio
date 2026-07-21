<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Project
{
    /**
     * Fixed, closed list of roles — grouped for display in the admin as checkboxes.
     * Not admin-extensible: adding a new role means adding it here.
     *
     * @var array<string, string[]>
     */
    public const ROLE_CHOICES = [
        'Product & stratégie' => ['Product Owner', 'Product Manager', 'Business Analyst'],
        'Design' => ['UX Designer', 'UI Designer', 'UX Researcher', 'Product Designer'],
        'Développement' => ['Tech Lead', 'Lead Developer', 'Développeur Front-end', 'Développeur Back-end', 'Développeur Full-stack', 'Développeur Mobile'],
        'Qualité & exploitation' => ['QA Engineer', 'Testeur', 'DevOps Engineer', 'SRE (Site Reliability Engineer)', 'Ingénieur Cloud'],
        'Gestion & coordination' => ['Scrum Master', 'Agile Coach', 'Project Manager', 'PMO'],
        'Autres rôles transverses' => ['Data Analyst', 'Data Scientist', 'Architecte solution', 'Expert sécurité / RSSI', 'Rédacteur technique', 'Support client', 'Customer Success'],
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionEn = null;

    #[ORM\Column(length: 255)]
    private ?string $stack = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $context = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contextEn = null;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $role = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectives = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectivesEn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $features = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $featuresEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tools = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sourceUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $demoUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $techDocName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverVideoName = null;

    #[ORM\Column]
    private bool $featured = false;

    #[ORM\Column]
    private bool $published = false;

    #[ORM\Column]
    private bool $wip = false;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'L\'ordre d\'affichage ne peut pas être négatif.')]
    private int $position = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, ProjectImage>
     */
    #[ORM\OneToMany(targetEntity: ProjectImage::class, mappedBy: 'project', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

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

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(?string $descriptionEn): static
    {
        $this->descriptionEn = $descriptionEn;

        return $this;
    }

    public function getLocalizedDescription(string $locale): string
    {
        if ('en' === $locale && $this->descriptionEn) {
            return $this->descriptionEn;
        }

        return $this->description ?? '';
    }

    public function getStack(): ?string
    {
        return $this->stack;
    }

    public function setStack(string $stack): static
    {
        $this->stack = $stack;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getStackList(): array
    {
        return array_filter(array_map('trim', explode(',', (string) $this->stack)));
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getContextEn(): ?string
    {
        return $this->contextEn;
    }

    public function setContextEn(?string $contextEn): static
    {
        $this->contextEn = $contextEn;

        return $this;
    }

    public function getLocalizedContext(string $locale): ?string
    {
        if ('en' === $locale && $this->contextEn) {
            return $this->contextEn;
        }

        return $this->context;
    }

    /**
     * @return string[]
     */
    public function getRole(): array
    {
        return $this->role ?? [];
    }

    /**
     * @param string[]|null $role
     */
    public function setRole(?array $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getObjectives(): ?string
    {
        return $this->objectives;
    }

    public function setObjectives(?string $objectives): static
    {
        $this->objectives = $objectives;

        return $this;
    }

    public function getObjectivesEn(): ?string
    {
        return $this->objectivesEn;
    }

    public function setObjectivesEn(?string $objectivesEn): static
    {
        $this->objectivesEn = $objectivesEn;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getLocalizedObjectiveLines(string $locale): array
    {
        $text = ('en' === $locale && $this->objectivesEn) ? $this->objectivesEn : $this->objectives;

        return array_values(array_filter(array_map('trim', explode("\n", (string) $text))));
    }

    public function getFeatures(): ?string
    {
        return $this->features;
    }

    public function setFeatures(?string $features): static
    {
        $this->features = $features;

        return $this;
    }

    public function getFeaturesEn(): ?string
    {
        return $this->featuresEn;
    }

    public function setFeaturesEn(?string $featuresEn): static
    {
        $this->featuresEn = $featuresEn;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getLocalizedFeatureLines(string $locale): array
    {
        $text = ('en' === $locale && $this->featuresEn) ? $this->featuresEn : $this->features;

        return array_values(array_filter(array_map('trim', explode("\n", (string) $text))));
    }

    public function getTools(): ?string
    {
        return $this->tools;
    }

    public function setTools(?string $tools): static
    {
        $this->tools = $tools;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getToolsList(): array
    {
        return array_filter(array_map('trim', explode(',', (string) $this->tools)));
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): static
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    public function getDemoUrl(): ?string
    {
        return $this->demoUrl;
    }

    public function setDemoUrl(?string $demoUrl): static
    {
        $this->demoUrl = $demoUrl;

        return $this;
    }

    public function getCoverVideoName(): ?string
    {
        return $this->coverVideoName;
    }

    public function setCoverVideoName(?string $coverVideoName): static
    {
        $this->coverVideoName = $coverVideoName;

        return $this;
    }

    public function getTechDocName(): ?string
    {
        return $this->techDocName;
    }

    public function setTechDocName(?string $techDocName): static
    {
        $this->techDocName = $techDocName;

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): static
    {
        $this->published = $published;

        return $this;
    }

    public function isWip(): bool
    {
        return $this->wip;
    }

    public function setWip(bool $wip): static
    {
        $this->wip = $wip;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, ProjectImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProjectImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProject($this);
        }

        return $this;
    }

    public function removeImage(ProjectImage $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getProject() === $this) {
                $image->setProject(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateSlug(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        if ($this->title && !$this->slug) {
            $this->slug = strtolower((new AsciiSlugger())->slug($this->title)->toString());
        }
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
