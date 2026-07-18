<?php

namespace App\Controller\Admin;

use App\Entity\Certificate;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class CertificateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Certificate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Certificat')
            ->setEntityLabelInPlural('Certificats')
            ->setDefaultSort(['position' => 'ASC', 'issueDate' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextField::new('issuer', 'Organisme');
        yield DateField::new('issueDate', 'Date d\'obtention')->hideOnIndex();
        yield UrlField::new('credentialUrl', 'Lien de vérification')->hideOnIndex();
        yield ImageField::new('badgeImageName', 'Badge / logo (optionnel)')
            ->setBasePath('/uploads/certificates')
            ->setUploadDir('public/uploads/certificates')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->hideOnIndex();
        yield BooleanField::new('published', 'Publié');
        yield IntegerField::new('position', 'Ordre d\'affichage')
            ->setFormTypeOption('attr', ['min' => 0]);
    }
}
