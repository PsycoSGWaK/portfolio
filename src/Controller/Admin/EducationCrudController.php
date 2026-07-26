<?php

namespace App\Controller\Admin;

use App\Entity\Education;
use App\Service\PositionReorderer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Validator\Constraints as Assert;

class EducationCrudController extends AbstractCrudController
{
    public function __construct(private readonly PositionReorderer $positionReorderer)
    {
    }

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
        yield TextField::new('degreeEn', 'Diplôme (EN)')
            ->setHelp('Optionnel — le site anglais affiche le texte français si ce champ est vide.')
            ->hideOnIndex();
        yield TextField::new('period', 'Période')
            ->setHelp('Texte libre, ex : 2022 - 2024');
        yield TextField::new('location', 'Lieu')->hideOnIndex();
        yield ImageField::new('logoName', 'Logo à uploader (optionnel)')
            ->setBasePath('/uploads/logos')
            ->setUploadDir('public/uploads/logos')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            // Le SVG est volontairement exclu : il peut embarquer du script,
            // et il serait servi depuis notre propre domaine.
            ->setFileConstraints(new Assert\Image(mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], maxSize: '4M'))
            ->hideOnIndex();
        yield UrlField::new('logoUrl', 'ou lien vers un logo')
            ->setHelp('Alternative à l\'upload : URL d\'une image déjà hébergée ailleurs. Prioritaire sur le logo uploadé si les deux sont renseignés.')
            ->hideOnIndex();
        yield TextareaField::new('description', 'Description')
            ->setHelp('Une puce par ligne.')
            ->hideOnIndex();
        yield TextareaField::new('descriptionEn', 'Description (EN)')
            ->setHelp('Optionnel — le site anglais affiche le texte français si ce champ est vide.')
            ->hideOnIndex();
        yield BooleanField::new('published', 'Publié');
        yield IntegerField::new('position', 'Ordre d\'affichage')
            ->setFormTypeOption('attr', ['min' => 0]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof Education);
        $this->positionReorderer->makeRoomForInsert(Education::class, $entityInstance->getPosition());

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof Education);
        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $oldPosition = $originalData['position'] ?? $entityInstance->getPosition();
        $this->positionReorderer->moveExisting(Education::class, $entityInstance->getId(), $oldPosition, $entityInstance->getPosition());

        parent::updateEntity($entityManager, $entityInstance);
    }
}
