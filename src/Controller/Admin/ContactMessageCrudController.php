<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContactMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContactMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Message')
            ->setEntityLabelInPlural('Messages reçus')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['name', 'email', 'company', 'message']);
    }

    /**
     * Les messages arrivent par le formulaire public : on ne les crée pas à la main.
     * Seul "traité" se modifie, d'où l'édition conservée mais la création retirée.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Reçu le')->setFormTypeOption('disabled', true);
        yield TextField::new('name', 'Nom')->setFormTypeOption('disabled', true);
        yield EmailField::new('email', 'E-mail')->setFormTypeOption('disabled', true);
        yield TextField::new('company', 'Société')->setFormTypeOption('disabled', true);
        yield TextareaField::new('message', 'Message')
            ->setFormTypeOption('disabled', true)
            ->setFormTypeOption('attr', ['rows' => 12])
            ->hideOnIndex();
        yield BooleanField::new('handled', 'Traité');
        yield TextField::new('ipAddress', 'IP')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex()
            ->setHelp('Conservée uniquement pour limiter les abus.');
    }
}
