<?php

namespace App\Controller\Admin;

use App\Entity\Skill;
use App\Service\PositionReorderer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SkillCrudController extends AbstractCrudController
{
    public function __construct(private readonly PositionReorderer $positionReorderer)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Skill::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Compétence')
            ->setEntityLabelInPlural('Compétences')
            ->setDefaultSort(['category' => 'ASC', 'position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('category', 'Catégorie');
        yield TextField::new('label', 'Nom');
        yield IntegerField::new('position', 'Ordre')
            ->setFormTypeOption('attr', ['min' => 0]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof Skill);
        $this->positionReorderer->makeRoomForInsert(
            Skill::class,
            $entityInstance->getPosition(),
            'e.category = :category',
            ['category' => $entityInstance->getCategory()]
        );

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof Skill);
        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $oldPosition = $originalData['position'] ?? $entityInstance->getPosition();
        $this->positionReorderer->moveExisting(
            Skill::class,
            $entityInstance->getId(),
            $oldPosition,
            $entityInstance->getPosition(),
            'e.category = :category',
            ['category' => $entityInstance->getCategory()]
        );

        parent::updateEntity($entityManager, $entityInstance);
    }
}
