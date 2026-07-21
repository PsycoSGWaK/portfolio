<?php

namespace App\Controller\Admin;

use App\Entity\SkillCategory;
use App\Service\PositionReorderer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SkillCategoryCrudController extends AbstractCrudController
{
    public function __construct(private readonly PositionReorderer $positionReorderer)
    {
    }

    public static function getEntityFqcn(): string
    {
        return SkillCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Catégorie de compétences')
            ->setEntityLabelInPlural('Compétences — Catégories')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('label', 'Nom de la catégorie')
            ->setHelp('Ex : Compétences techniques, Méthodologies, Langues, Soft skills...');
        yield TextField::new('labelEn', 'Nom de la catégorie (EN)')
            ->setHelp('Optionnel — le site anglais affiche le nom français si ce champ est vide.')
            ->hideOnIndex();
        yield TextField::new('icon', 'Icône (emoji)')
            ->setHelp('Un seul emoji affiché dans le rond coloré, ex : 💻 🛠️ 🗣️. Optionnel.')
            ->hideOnIndex();
        yield BooleanField::new('published', 'Publiée');
        yield IntegerField::new('position', 'Ordre d\'affichage')
            ->setFormTypeOption('attr', ['min' => 0]);
        yield AssociationField::new('skills', 'Compétences')->onlyOnIndex();
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof SkillCategory);
        $this->positionReorderer->makeRoomForInsert(SkillCategory::class, $entityInstance->getPosition());

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof SkillCategory);
        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $oldPosition = $originalData['position'] ?? $entityInstance->getPosition();
        $this->positionReorderer->moveExisting(SkillCategory::class, $entityInstance->getId(), $oldPosition, $entityInstance->getPosition());

        parent::updateEntity($entityManager, $entityInstance);
    }
}
