<?php

namespace App\Twig;

use Twig\Attribute\AsTwigFunction;

class TechIconExtension
{
    /**
     * Maps a lowercased first word of a stack/tool label to a Simple Icons slug
     * (see templates/_tech_icon.html.twig). Unmatched labels render without a logo.
     */
    private const SLUGS = [
        'php' => 'php',
        'symfony' => 'symfony',
        'mysql' => 'mysql',
        'mariadb' => 'mariadb',
        'javascript' => 'javascript',
        'js' => 'javascript',
        'typescript' => 'typescript',
        'ts' => 'typescript',
        'html' => 'html5',
        'html5' => 'html5',
        'css' => 'css',
        'css3' => 'css',
        'bootstrap' => 'bootstrap',
        'jquery' => 'jquery',
        'node' => 'nodedotjs',
        'nodejs' => 'nodedotjs',
        'node.js' => 'nodedotjs',
        'react' => 'react',
        'vue' => 'vuedotjs',
        'vuejs' => 'vuedotjs',
        'vue.js' => 'vuedotjs',
        'angular' => 'angular',
        'docker' => 'docker',
        'git' => 'git',
        'github' => 'github',
        'gitlab' => 'gitlab',
        'python' => 'python',
        'java' => 'openjdk',
        'linux' => 'linux',
        'wordpress' => 'wordpress',
        'laravel' => 'laravel',
        'sass' => 'sass',
        'tailwind' => 'tailwindcss',
        'tailwindcss' => 'tailwindcss',
        'composer' => 'composer',
        'phpmyadmin' => 'phpmyadmin',
        'postgresql' => 'postgresql',
        'postgres' => 'postgresql',
        'sqlite' => 'sqlite',
        'apache' => 'apache',
        'nginx' => 'nginx',
        'vercel' => 'vercel',
        'netlify' => 'netlify',
        'phpstorm' => 'phpstorm',
        'intellij' => 'intellijidea',
        'intellijidea' => 'intellijidea',
        'webstorm' => 'webstorm',
        'notepad++' => 'notepadplusplus',
        'notepadplusplus' => 'notepadplusplus',
        'filezilla' => 'filezilla',
        'gitkraken' => 'gitkraken',
        'postman' => 'postman',
        'insomnia' => 'insomnia',
        'swagger' => 'swagger',
        'discord' => 'discord',
        'figma' => 'figma',
        'jira' => 'jira',
        'trello' => 'trello',
        'notion' => 'notion',
    ];

    #[AsTwigFunction('tech_icon_slug')]
    public function slugFor(string $label): ?string
    {
        $firstWord = strtolower(strtok(trim($label), " \t"));

        return self::SLUGS[$firstWord] ?? null;
    }
}
