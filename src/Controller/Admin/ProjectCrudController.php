<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Service\PositionReorderer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
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
        yield ChoiceField::new('role', 'Rôle')
            ->setChoices($this->buildRoleChoices())
            ->allowMultipleChoices()
            ->renderExpanded()
            ->setHelp('Coche tous les rôles applicables à ce projet.')
            ->hideOnIndex();
        yield TextareaField::new('objectives', 'Objectifs / Cahier des charges')
            ->setHelp('Une puce par ligne : ce que le projet devait accomplir, les besoins ou contraintes à respecter (pas les étapes déjà réalisées).')
            ->hideOnIndex();
        yield TextareaField::new('features', 'Fonctionnalités importantes')
            ->setHelp('Une puce par ligne : les fonctionnalités clés livrées.')
            ->hideOnIndex();
        yield TextField::new('stack', 'Stack technique')
            ->setHelp('Séparée par des virgules, ex : Symfony, PHP, MySQL, Docker. Un logo s\'affiche automatiquement pour les technos reconnues.');
        yield TextField::new('tools', 'Outils / logiciels utilisés')
            ->setHelp('Séparée par des virgules, ex : Figma, Postman, Docker, VS Code. Un logo s\'affiche automatiquement pour les outils reconnus.')
            ->hideOnIndex();
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
        yield FileField::new('techDocName', 'Documentation technique (PDF, optionnelle)')
            ->setBasePath('/uploads/projects')
            ->setUploadDir('public/uploads/projects')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFileConstraints(new Assert\File(mimeTypes: ['application/pdf']))
            ->setHelp('Affiche un bouton de téléchargement à côté du lien source sur la page détail.')
            ->hideOnIndex();
        yield BooleanField::new('featured', 'Mis en avant');
        yield BooleanField::new('published', 'Publié');
        yield IntegerField::new('position', 'Ordre d\'affichage')
            ->setFormTypeOption('attr', ['min' => 0]);
        yield AssociationField::new('images', 'Images')->onlyOnIndex();
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function buildRoleChoices(): array
    {
        $choices = [];
        foreach (Project::ROLE_CHOICES as $group => $roles) {
            $choices[$group] = array_combine($roles, $roles);
        }

        return $choices;
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
