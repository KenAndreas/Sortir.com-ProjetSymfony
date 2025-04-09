<?php
// src/Form/ProfileType.php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
->add('mail', EmailType::class)
->add('pseudo', TextType::class)
->add('campus', EntityType::class, [
'class' => Campus::class,
'choice_label' => 'nom',
'disabled' => true, // Le champ campus est visible mais non modifiable
])
    ->add('photo', FileType::class, [
        'label' => 'Photo de profil',
        'required' => false,  // Optionnel
        'mapped' => false,    // Ne pas mapper directement à l'entité (car c'est un fichier)
        'attr' => ['accept' => 'image/*'], // Limiter les types de fichiers aux images
    ])
    ->add('motDePasse', PasswordType::class, [
'required' => true,  // Mot de passe obligatoire
'label' => 'Mot de passe',
]);
}

public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults([
'data_class' => Participant::class,
]);
}
}
