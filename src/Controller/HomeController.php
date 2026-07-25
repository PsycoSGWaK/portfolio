<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactMessageType;
use App\Repository\CertificateRepository;
use App\Repository\ContactMessageRepository;
use App\Repository\ExperienceRepository;
use App\Repository\EducationRepository;
use App\Repository\ProfileRepository;
use App\Repository\ProjectRepository;
use App\Repository\SkillCategoryRepository;
use App\Service\ContactNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    /** Fenêtre et plafond du garde-fou anti-flood, par adresse IP. */
    private const THROTTLE_MINUTES = 10;
    private const THROTTLE_MAX = 3;

    #[Route(path: ['fr' => '/', 'en' => '/en'], name: 'home')]
    public function index(Request $request, ProjectRepository $projectRepository, CertificateRepository $certificateRepository, ExperienceRepository $experienceRepository, EducationRepository $educationRepository, ProfileRepository $profileRepository, SkillCategoryRepository $skillCategoryRepository, ContactMessageRepository $contactMessageRepository, EntityManagerInterface $entityManager, ContactNotifier $contactNotifier): Response
    {
        $locale = $request->getLocale();
        $profile = $profileRepository->getSingleton();

        $contactMessage = new ContactMessage();
        $contactForm = $this->createForm(ContactMessageType::class, $contactMessage);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $ip = (string) $request->getClientIp();

            // Honeypot rempli ou envois rapprochés depuis la même IP : on fait comme si
            // tout s'était bien passé plutôt que d'afficher une erreur, pour ne pas
            // indiquer au robot ce qui l'a bloqué.
            $isSpam = (bool) $contactForm->get('website')->getData()
                || $contactMessageRepository->countRecentFromIp($ip, self::THROTTLE_MINUTES) >= self::THROTTLE_MAX;

            if (!$isSpam) {
                $contactMessage->setIpAddress($ip);
                $entityManager->persist($contactMessage);
                $entityManager->flush();

                // Le message est déjà en base : l'échec d'envoi n'est pas bloquant.
                $contactNotifier->notify($contactMessage, $profile?->getContactEmail());
            }

            $this->addFlash('contact_success', 'Merci, votre message est bien parti. Je vous réponds rapidement.');

            return $this->redirectToRoute('home', ['_fragment' => 'contact']);
        }
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
            'contactForm' => $contactForm,
            'skillCategories' => $skillCategories,
            'profile' => $profile,
            'experiences' => $experienceRepository->findPublishedOrderedByPosition(),
            'educations' => $educationRepository->findPublishedOrderedByPosition(),
            'projects' => $projectRepository->findPublishedOrderedByPosition(),
            'certificates' => $certificateRepository->findPublishedOrderedByPosition(),
        ]);
    }
}
