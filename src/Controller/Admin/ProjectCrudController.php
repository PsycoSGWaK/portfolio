<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Service\PositionReorderer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FileField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectCrudController extends AbstractCrudController
{
    public function __construct(private readonly PositionReorderer $positionReorderer)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Projet')
            ->setEntityLabelInPlural('Projets')
            ->setDefaultSort(['position' => 'ASC', 'createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextField::new('slug')->hideOnForm();
        yield TextareaField::new('description', 'Description');
        yield TextareaField::new('context', 'Contexte')
            ->setHelp('Pourquoi ce projet, pour qui (perso, scolaire, stage...).')
            ->hideOnIndex();
        yield TextField::new('role', 'Rôle')
            ->setHelp('Ton rôle sur le projet, ex : Projet personnel, Projet de fin d\'études, Stage...')
            ->hideOnIndex();
        yield TextareaField::new('approach', 'Démarche')
            ->setHelp('Une puce par ligne : étapes clés ou défis techniques résolus.')
            ->hideOnIndex();
        yield TextField::new('stack', 'Stack technique')
            ->setHelp('Séparée par des virgules, ex : Symfony, PHP, MySQL, Docker');
        yield UrlField::new('sourceUrl', 'Lien code source')
            ->setHelp('GitHub, GitLab, ou un autre dépôt — l\'icône affichée sur le site s\'adapte automatiquement.')
            ->hideOnIndex();
        yield UrlField::new('demoUrl', 'Lien démo')->hideOnIndex();
        yield FileField::new('coverVideoName', 'Vidéo de couverture (optionnelle)')
            ->setBasePath('/uploads/projects')
            ->setUploadDir('public/uploads/projects')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFileConstraints(new Assert\File(mimeTypes: ['video/mp4', 'video/webm']))
            ->setHelp('Court clip en boucle (mp4/webm), affiché à la place de l\'image sur la carte d\'accueil.')
            ->hideOnIndex();
        yield BooleanField::new('featured', 'Mis en avant');
        yield BooleanField::new('published', 'Publié');
        yield IntegerField::new('position', 'Ordre d\'affichage')
            ->setFormTypeOption('attr', ['min' => 0]);
        yield AssociationField::new('images', 'Images')->onlyOnIndex();
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof Project);
        $this->positionReorderer->makeRoomForInsert(Project::class, $entityInstance->getPosition());

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof Project);
        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $oldPosition = $originalData['position'] ?? $entityInstance->getPosition();
        $this->positionReorderer->moveExisting(Project::class, $entityInstance->getId(), $oldPosition, $entityInstance->getPosition());

        parent::updateEntity($entityManager, $entityInstance);
    }
}
