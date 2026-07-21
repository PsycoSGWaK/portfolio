<?php

namespace App\Controller;

use App\Repository\CertificateRepository;
use App\Repository\ExperienceRepository;
use App\Repository\EducationRepository;
use App\Repository\ProfileRepository;
use App\Repository\ProjectRepository;
use App\Repository\SkillCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route(path: ['fr' => '/', 'en' => '/en'], name: 'home')]
    public function index(Request $request, ProjectRepository $projectRepository, CertificateRepository $certificateRepository, ExperienceRepository $experienceRepository, EducationRepository $educationRepository, ProfileRepository $profileRepository, SkillCategoryRepository $skillCategoryRepository): Response
    {
        $locale = $request->getLocale();
        $profile = $profileRepository->getSingleton();
        $skillCategories = $skillCategoryRepository->findPublishedOrderedByPosition();

        $about = $profile ? $profile->getLocalizedAboutParagraphs($locale) : [];
        $highlights = $profile ? $profile->getLocalizedHighlightLines($locale) : [];
        $stats = $profile ? $profile->getLocalizedStatsList($locale) : [];

        return $this->render('home/index.html.twig', [
            'name' => 'Guillaume Hurard',
            'about' => $about,
            'highlights' => $highlights,
            'stats' => $stats,
            'hasAbout' => $about || $highlights || $stats,
            'skillCategories' => $skillCategories,
            'profile' => $profile,
            'experiences' => $experienceRepository->findPublishedOrderedByPosition(),
            'educations' => $educationRepository->findPublishedOrderedByPosition(),
            'projects' => $projectRepository->findPublishedOrderedByPosition(),
            'certificates' => $certificateRepository->findPublishedOrderedByPosition(),
        ]);
    }
}
