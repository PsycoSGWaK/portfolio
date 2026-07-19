<?php

namespace App\Twig;

use Twig\Attribute\AsTwigFunction;

class SkillCategoryColorExtension
{
    /**
     * Rotating palette used to give each skill category card a distinct
     * accent color, since categories are admin-created and open-ended
     * (no fixed list to hardcode colors against).
     */
    private const PALETTE = [
        '#7AB6D9',
        '#E17497',
        '#4FC3A1',
        '#E2735A',
        '#B98CE0',
        '#E0B84F',
        '#6FA8DC',
        '#7FCF6E',
    ];

    #[AsTwigFunction('skillCategoryColor')]
    public function colorForIndex(int $index): string
    {
        return self::PALETTE[$index % \count(self::PALETTE)];
    }
}
