<?php

namespace App\Controller\Admin;

use App\Entity\Profile;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ProfileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Profile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Profil / À propos')
            ->setEntityLabelInPlural('Profil / À propos');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield ImageField::new('photoName', 'Photo')
            ->setBasePath('/uploads/profile')
            ->setUploadDir('public/uploads/profile')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setHelp('Remplace la photo affichée dans la section « À propos ». Laisse vide pour garder l\'image par défaut.');
        yield TextareaField::new('aboutText', 'Texte « À propos »')
            ->setHelp('Un paragraphe par ligne. Laisse vide pour garder le texte par défaut.')
            ->setFormTypeOption('attr', ['rows' => 10]);
        yield TextareaField::new('highlights', 'Points forts')
            ->setHelp('Une puce par ligne. Laisse vide pour garder la liste par défaut.');
        yield TextareaField::new('stats', 'Statistiques')
            ->setHelp('Une statistique par ligne, au format "nombre | libellé", ex : 3+ | ans chez Safran Aircraft Engines. Laisse vide pour garder les stats par défaut.');
    }
}
