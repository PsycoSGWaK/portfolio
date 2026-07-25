<?php

namespace App\Controller\Admin;

use App\Entity\Certificate;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Project;
use App\Entity\SkillCategory;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function index(): Response
    {
        $stats = [];
        foreach ([
            'Projets' => [Project::class, ProjectCrudController::class],
            'Certificats' => [Certificate::class, CertificateCrudController::class],
            'Expériences' => [Experience::class, ExperienceCrudController::class],
            'Parcours académique' => [Education::class, EducationCrudController::class],
            'Catégories de compétences' => [SkillCategory::class, SkillCategoryCrudController::class],
        ] as $label => [$entityClass, $crudController]) {
            $repository = $this->entityManager->getRepository($entityClass);
            $stats[] = [
                'label' => $label,
                'total' => $repository->count([]),
                'published' => $repository->count(['published' => true]),
                'crudController' => $crudController,
            ];
        }

        return $this->render('admin/dashboard.html.twig', ['stats' => $stats]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Portfolio Admin');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addAssetMapperEntry('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkTo(ProfileCrudController::class, 'Profil / À propos', 'fa fa-user')
            ->setAction('edit')
            ->setEntityId(1);
        yield MenuItem::linkTo(SkillCategoryCrudController::class, 'Compétences — Catégories', 'fa fa-layer-group');
        yield MenuItem::linkTo(SkillCrudController::class, 'Compétences', 'fa fa-check');
        yield MenuItem::linkTo(ExperienceCrudController::class, 'Expériences', 'fa fa-briefcase');
        yield MenuItem::linkTo(EducationCrudController::class, 'Parcours académique', 'fa fa-graduation-cap');
        yield MenuItem::linkTo(ProjectCrudController::class, 'Projets', 'fa fa-diagram-project');
        yield MenuItem::linkTo(ProjectImageCrudController::class, 'Images', 'fa fa-images');
        yield MenuItem::linkTo(CertificateCrudController::class, 'Certificats', 'fa fa-certificate');
        yield MenuItem::section('Contact');
        yield MenuItem::linkTo(ContactMessageCrudController::class, 'Messages reçus', 'fa fa-envelope');
        yield MenuItem::section('Données');
        yield MenuItem::linkToRoute('Exporter les données', 'fa fa-download', 'admin_data_export');
        yield MenuItem::linkToRoute('Importer des données', 'fa fa-upload', 'admin_data_import');
    }
}
