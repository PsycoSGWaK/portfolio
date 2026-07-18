<?php

namespace App\Controller\Admin;

use App\Entity\Education;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class EducationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Education::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Formation')
            ->setEntityLabelInPlural('Parcours académique')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('school', 'École');
        yield TextField::new('degree', 'Diplôme');
        yield TextField::new('period', 'Période')
            ->setHelp('Texte libre, ex : 2022 - 2024');
        yield TextField::new('location', 'Lieu')->hideOnIndex();
        yield ImageField::new('logoName', 'Logo à uploader (optionnel)')
            ->setBasePath('/uploads/logos')
            ->setUploadDir('public/uploads/logos')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->hideOnIndex();
        yield UrlField::new('logoUrl', 'ou lien vers un logo')
            ->setHelp('Alternative à l\'upload : URL d\'une image déjà hébergée ailleurs. Prioritaire sur le logo uploadé si les deux sont renseignés.')
            ->hideOnIndex();
        yield TextareaField::new('description', 'Description')
            ->setHelp('Une puce par ligne.')
            ->hideOnIndex();
        yield BooleanField::new('published', 'Publié');
        yield IntegerField::new('position', 'Ordre d\'affichage')
            ->setFormTypeOption('attr', ['min' => 0]);
    }
}
