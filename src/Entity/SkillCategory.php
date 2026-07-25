<?php

namespace App\Entity;

use App\Repository\SkillCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillCategoryRepository::class)]
class SkillCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $labelEn = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private bool $published = false;

    #[ORM\Column]
    private int $position = 0;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\OneToMany(targetEntity: Skill::class, mappedBy: 'category', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $skills;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getLabelEn(): ?string
    {
        return $this->labelEn;
    }

    public function setLabelEn(?string $labelEn): static
    {
        $this->labelEn = $labelEn;

        return $this;
    }

    public function getLocalizedLabel(string $locale): string
    {
        if ('en' === $locale && $this->labelEn) {
            return $this->labelEn;
        }

        return $this->label ?? '';
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setCategory($this);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        if ($this->skills->removeElement($skill)) {
            if ($skill->getCategory() === $this) {
                $skill->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->label ?? '';
    }
}
