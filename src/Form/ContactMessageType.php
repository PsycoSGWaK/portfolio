<?php

namespace App\Form;

use App\Entity\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre nom',
                'attr' => ['autocomplete' => 'name'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre e-mail',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('company', TextType::class, [
                'label' => 'Société (optionnel)',
                'required' => false,
                'attr' => ['autocomplete' => 'organization'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => ['rows' => 6],
            ])
            // Piège à robots : invisible et hors du flux de tabulation, un humain ne
            // le remplit jamais. Non mappé sur l'entité, il n'est donc pas persisté.
            ->add('website', TextType::class, [
                'label' => 'Ne pas remplir',
                'required' => false,
                'mapped' => false,
                'attr' => ['autocomplete' => 'off', 'tabindex' => '-1'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
        ]);
    }
}
