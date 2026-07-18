<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): RedirectResponse
    {
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(ProjectCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Portfolio Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkTo(ExperienceCrudController::class, 'Expériences', 'fa fa-briefcase');
        yield MenuItem::linkTo(ProjectCrudController::class, 'Projets', 'fa fa-diagram-project');
        yield MenuItem::linkTo(ProjectImageCrudController::class, 'Images', 'fa fa-images');
        yield MenuItem::linkTo(CertificateCrudController::class, 'Certificats', 'fa fa-certificate');
    }
}
