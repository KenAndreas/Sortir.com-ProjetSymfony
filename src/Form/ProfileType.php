<?php
// src/Form/ProfileType.php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('telephone', TelType::class, ['required' => false])
            ->add('mail', EmailType::class, ['required' => true])
            ->add('pseudo', TextType::class, ['required' => true])
            ->add('campus', EntityType::class, [
                'required' => true,
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('motDePasse', PasswordType::class, [
                'required' => true,  // Mot de passe obligatoire
                'label' => ' mot de passe',
            ])
            ->add('actif', CheckboxType::class, [
                'label' => ' actif',
                'required' => false,
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,  // Optionnel
                'mapped' => false,    // Ne pas mapper directement à l'entité (car c'est un fichier)
                'attr' => ['accept' => 'image/*'], // Limiter les types de fichiers aux images
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}

