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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedinUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $aboutText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $aboutTextEn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $highlights = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $highlightsEn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stats = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $statsEn = null;

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

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getLinkedinUrl(): ?string
    {
        return $this->linkedinUrl;
    }

    public function setLinkedinUrl(?string $linkedinUrl): static
    {
        $this->linkedinUrl = $linkedinUrl;

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

    public function getAboutTextEn(): ?string
    {
        return $this->aboutTextEn;
    }

    public function setAboutTextEn(?string $aboutTextEn): static
    {
        $this->aboutTextEn = $aboutTextEn;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getLocalizedAboutParagraphs(string $locale): array
    {
        $text = ('en' === $locale && $this->aboutTextEn) ? $this->aboutTextEn : $this->aboutText;

        return array_values(array_filter(array_map('trim', explode("\n", (string) $text))));
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

    public function getHighlightsEn(): ?string
    {
        return $this->highlightsEn;
    }

    public function setHighlightsEn(?string $highlightsEn): static
    {
        $this->highlightsEn = $highlightsEn;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getLocalizedHighlightLines(string $locale): array
    {
        $text = ('en' === $locale && $this->highlightsEn) ? $this->highlightsEn : $this->highlights;

        return array_values(array_filter(array_map('trim', explode("\n", (string) $text))));
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

    public function getStatsEn(): ?string
    {
        return $this->statsEn;
    }

    public function setStatsEn(?string $statsEn): static
    {
        $this->statsEn = $statsEn;

        return $this;
    }

    /**
     * @return array<int, array{number: string, label: string}>
     */
    public function getLocalizedStatsList(string $locale): array
    {
        return self::parseStats(('en' === $locale && $this->statsEn) ? $this->statsEn : $this->stats);
    }

    /**
     * @return array<int, array{number: string, label: string}>
     */
    private static function parseStats(?string $stats): array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", (string) $stats))));

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
