<?php

namespace App\Controller\Admin;

use App\Entity\ProjectImage;
use App\Service\PositionReorderer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectImageCrudController extends AbstractCrudController
{
    public function __construct(private readonly PositionReorderer $positionReorderer)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ProjectImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image')
            ->setEntityLabelInPlural('Images')
            ->setDefaultSort(['project' => 'ASC', 'position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('project', 'Projet');
        yield ImageField::new('imageName', 'Image')
            ->setBasePath('/uploads/projects')
            ->setUploadDir('public/uploads/projects')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            // Le SVG est volontairement exclu : il peut embarquer du script,
            // et il serait servi depuis notre propre domaine.
            ->setFileConstraints(new Assert\Image(mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], maxSize: '4M'));
        yield IntegerField::new('position', 'Ordre')
            ->setFormTypeOption('attr', ['min' => 0]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof ProjectImage);
        $this->positionReorderer->makeRoomForInsert(
            ProjectImage::class,
            $entityInstance->getPosition(),
            'e.project = :project',
            ['project' => $entityInstance->getProject()]
        );

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        \assert($entityInstance instanceof ProjectImage);
        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $oldPosition = $originalData['position'] ?? $entityInstance->getPosition();
        $this->positionReorderer->moveExisting(
            ProjectImage::class,
            $entityInstance->getId(),
            $oldPosition,
            $entityInstance->getPosition(),
            'e.project = :project',
            ['project' => $entityInstance->getProject()]
        );

        parent::updateEntity($entityManager, $entityInstance);
    }
}
