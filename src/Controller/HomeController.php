<?php

namespace App\Controller;

use App\Repository\CertificateRepository;
use App\Repository\ExperienceRepository;
use App\Repository\EducationRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProjectRepository $projectRepository, CertificateRepository $certificateRepository, ExperienceRepository $experienceRepository): Response
    public function index(ProjectRepository $projectRepository, CertificateRepository $certificateRepository, EducationRepository $educationRepository): Response
    {
        $skills = ['PHP', 'Symfony', 'JavaScript', 'Angular', 'Git', 'Agile/Scrum'];

        return $this->render('home/index.html.twig', [
            'name' => 'Guillaume Hurard',
            'about' => [
                "Développeur et gestionnaire de projet passionné par les solutions digitales innovantes, je combine expertise technique et vision stratégique pour concevoir des outils performants et adaptés aux besoins utilisateurs. Avec une expérience solide en développement d'applications (JavaScript, PHP, Angular) et en gestion de projet Agile, j'ai contribué à des projets ambitieux, comme la migration d'outils vers un environnement commun chez Safran Aircraft Engines, ou la création d'une application de monitoring pour le service DSI de Bel.",
                "Mon parcours académique (Mastère Expert IT, Licence Développeur de solutions digitales) et mes alternances chez Safran, CentraleSupélec Alumni et Bel m'ont permis d'acquérir une expertise en planification, coordination et livraison de projets, toujours dans le respect des délais et des attentes clients. Je m'épanouis dans des environnements dynamiques où la créativité et la rigueur se rencontrent pour résoudre des problèmes concrets.",
                "En dehors du code, je cultive ma passion pour les jeux vidéo en développant des applications dédiées, et j'aime partager mes connaissances (BAFA, certifications Iterop).",
            ],
            'highlights' => [
                'Développement full-stack (JavaScript, PHP, Angular, SQL)',
                'Méthodologies Agile (Scrum, Kanban) et Cycle en V',
                'Gestion de projet et migration technologique',
                'Résolution de problèmes et optimisation de processus',
            ],
            'stats' => [
                ['number' => ((int) date('Y') - 2022) . '+', 'label' => 'ans chez Safran Aircraft Engines'],
                ['number' => '99,99 %', 'label' => 'de disponibilité obtenue sur Iterop'],
                ['number' => (string) count($skills), 'label' => 'technologies maîtrisées'],
            ],
            'skills' => $skills,
            'experiences' => $experienceRepository->findPublishedOrderedByPosition(),
            'educations' => $educationRepository->findPublishedOrderedByPosition(),
            'projects' => $projectRepository->findPublishedOrderedByPosition(),
            'certificates' => $certificateRepository->findPublishedOrderedByPosition(),
        ]);
    }
}
