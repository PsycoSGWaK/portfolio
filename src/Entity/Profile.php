<?php

namespace App\Entity;

use App\Repository\ProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $aboutText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $highlights = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stats = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhotoName(): ?string
    {
        return $this->photoName;
    }

    public function setPhotoName(?string $photoName): static
    {
        $this->photoName = $photoName;

        return $this;
    }

    public function getAboutText(): ?string
    {
        return $this->aboutText;
    }

    public function setAboutText(?string $aboutText): static
    {
        $this->aboutText = $aboutText;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAboutParagraphs(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", (string) $this->aboutText))));
    }

    public function getHighlights(): ?string
    {
        return $this->highlights;
    }

    public function setHighlights(?string $highlights): static
    {
        $this->highlights = $highlights;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getHighlightLines(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", (string) $this->highlights))));
    }

    public function getStats(): ?string
    {
        return $this->stats;
    }

    public function setStats(?string $stats): static
    {
        $this->stats = $stats;

        return $this;
    }

    /**
     * @return array<int, array{number: string, label: string}>
     */
    public function getStatsList(): array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", (string) $this->stats))));

        return array_values(array_filter(array_map(static function (string $line): ?array {
            $parts = explode('|', $line, 2);
            if (2 !== \count($parts)) {
                return null;
            }

            return ['number' => trim($parts[0]), 'label' => trim($parts[1])];
        }, $lines)));
    }

    public function __toString(): string
    {
        return 'Profil / À propos';
    }
}
