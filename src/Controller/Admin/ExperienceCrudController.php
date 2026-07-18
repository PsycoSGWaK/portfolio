<?php

namespace App\Controller\Admin;

use App\Entity\Experience;
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

class ExperienceCrudController extends AbstractCrudController
{
    public function __construct(private readonly PositionReorderer $positionReorderer)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Experience::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Expérience')
            ->setEntityLabelInPlural('Expériences')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('company', 'Entreprise');
        yield TextField::new('role', 'Poste');
        yield TextField::new('period', 'Période')
            ->setHelp('Texte libre, ex : 2022 - Présent');
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

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof Experience);
        $this->positionReorderer->makeRoomForInsert(Experience::class, $entityInstance->getPosition());

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof Experience);
        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $oldPosition = $originalData['position'] ?? $entityInstance->getPosition();
        $this->positionReorderer->moveExisting(Experience::class, $entityInstance->getId(), $oldPosition, $entityInstance->getPosition());

        parent::updateEntity($entityManager, $entityInstance);
    }
}
