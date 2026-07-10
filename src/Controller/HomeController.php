<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'name' => 'Guillaume Hurard',
            'about' => [
                "Développeur chez Safran Aircraft Engines depuis 2022, je conçois des outils numériques qui simplifient le quotidien des équipes : d'une application de contrôle qualité qui a fait chuter les non-conformités, à la migration complète de notre outil de gestion de processus vers Iterop — livrée en moins de cinq mois et qui a fait passer notre disponibilité de 90 % à 99,99 %. Avant Safran, j'ai fait mes armes chez CentraleSupélec Alumni en développant des outils d'analyse de données en JavaScript, puis chez Bel où j'ai conçu en Angular une application de supervision pour toute la DSI, tout en assurant le support technique quotidien des utilisateurs.",
                "Formé à l'IRIS École Supérieure d'Informatique jusqu'au Mastère Expert IT, développement et big data, j'aime autant écrire du code que piloter un projet de bout en bout — cadrage des besoins, méthode agile, livraison. Mon terrain de jeu technique : PHP, Symfony, JavaScript, avec Git comme réflexe. En dehors du travail, je code aussi pour le plaisir : des applications qui optimisent la stratégie dans mes jeux vidéo préférés.",
            ],
            'projects' => $projectRepository->findPublishedOrderedByPosition(),
        ]);
    }
}
