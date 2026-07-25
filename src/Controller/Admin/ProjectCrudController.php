<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Service\PositionReorderer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new('duplicate', 'Dupliquer', 'fa fa-copy')
            ->linkToCrudAction('duplicate');

        return $actions
            ->add(Crud::PAGE_INDEX, $duplicate)
            ->add(Crud::PAGE_DETAIL, $duplicate);
    }

    #[AdminRoute('/duplicate')]
    public function duplicate(AdminContext $context, EntityManagerInterface $entityManager): RedirectResponse
    {
        $original = $context->getEntity()->getInstance();
        \assert($original instanceof Project);

        $duplicate = new Project();
        $duplicate->setTitle($original->getTitle() . ' (copie)');
        $duplicate->setDescription($original->getDescription());
        $duplicate->setDescriptionEn($original->getDescriptionEn());
        $duplicate->setContext($original->getContext());
        $duplicate->setContextEn($original->getContextEn());
        $duplicate->setRole($original->getRole());
        $duplicate->setObjectives($original->getObjectives());
        $duplicate->setObjectivesEn($original->getObjectivesEn());
        $duplicate->setFeatures($original->getFeatures());
        $duplicate->setFeaturesEn($original->getFeaturesEn());
        $duplicate->setStack($original->getStack());
        $duplicate->setTools($original->getTools());
        $duplicate->setSourceUrl($original->getSourceUrl());
        $duplicate->setDemoUrl($original->getDemoUrl());
        $duplicate->setCoverVideoName($original->getCoverVideoName());
        $duplicate->setTechDocName($original->getTechDocName());
        $duplicate->setFeatured(false);
        $duplicate->setPublished(false);
        $duplicate->setWip($original->isWip());
        $duplicate->setPosition($original->getPosition() + 1);

        foreach ($original->getImages() as $image) {
            $duplicateImage = new ProjectImage();
            $duplicateImage->setImageName($image->getImageName());
            $duplicateImage->setPosition($image->getPosition());
            $duplicate->addImage($duplicateImage);
        }

        $this->positionReorderer->makeRoomForInsert(Project::class, $duplicate->getPosition());
        $entityManager->persist($duplicate);
        $entityManager->flush();

        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($duplicate->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextField::new('slug')->hideOnForm();
        yield TextareaField::new('description', 'Description');
        yield TextareaField::new('descriptionEn', 'Description (EN)')
            ->setHelp('Optionnel — le site anglais affiche le texte français si ce champ est vide.')
            ->hideOnIndex();
        yield TextareaField::new('context', 'Contexte')
            ->setHelp('Pourquoi ce projet, pour qui (perso, scolaire, stage...).')
            ->hideOnIndex();
        yield TextareaField::new('contextEn', 'Contexte (EN)')
            ->setHelp('Optionnel — le site anglais affiche le texte français si ce champ est vide.')
            ->hideOnIndex();
        yield ChoiceField::new('role', 'Rôle')
            ->setChoices($this->buildRoleChoices())
            ->allowMultipleChoices()
            ->renderExpanded()
            ->setCssClass('project-role-checkboxes')
            ->setHelp('Coche tous les rôles applicables à ce projet.')
            ->hideOnIndex();
        yield TextareaField::new('objectives', 'Objectifs / Cahier des charges')
            ->setHelp('Une puce par ligne : ce que le projet devait accomplir, les besoins ou contraintes à respecter (pas les étapes déjà réalisées).')
            ->hideOnIndex();
        yield TextareaField::new('objectivesEn', 'Objectifs (EN)')
            ->setHelp('Optionnel — le site anglais affiche le texte français si ce champ est vide.')
            ->hideOnIndex();
        yield TextareaField::new('features', 'Fonctionnalités importantes')
            ->setHelp('Une puce par ligne : les fonctionnalités clés livrées.')
            ->hideOnIndex();
        yield TextareaField::new('featuresEn', 'Fonctionnalités importantes (EN)')
            ->setHelp('Optionnel — le site anglais affiche le texte français si ce champ est vide.')
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
        yield BooleanField::new('wip', 'En cours (WIP)')
            ->setHelp('La carte affiche "Titre - Work in progress" et ne mène à aucune page détail.');
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
