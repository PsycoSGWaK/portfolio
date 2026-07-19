<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    #[Route(path: ['fr' => '/projets/{slug}', 'en' => '/en/projects/{slug}'], name: 'project_show')]
    public function show(string $slug, ProjectRepository $projectRepository): Response
    {
        $project = $projectRepository->findOnePublishedBySlug($slug);
        if (!$project) {
            throw $this->createNotFoundException('Projet introuvable.');
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }
}
