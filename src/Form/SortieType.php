<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class, [
                'attr' => ['class' => 'form-control form-control-sm'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                    new Length(['max' => 255, 'maxMessage' => 'Le nom ne peut dépasser 255 caractères']),
                ]
            ])
            ->add('dateHeureDebut', DateTImeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control form-control-sm'],
            ])
            ->add('duree', TimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control form-control-sm'],
                'constraints' => [
                    new NotBlank(['message' => 'Le heure est obligatoire']),
                ]
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control form-control-sm'],
            ])
            ->add('nbInscriptionMax', NumberType::class)
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-control form-control-sm']
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-control form-control-sm'],
            ])
            ->add('infosSortie',TextareaType::class, [
                'attr' => ['class' => 'form-control form-control-sm'],
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255, 'maxMessage' => 'Les infos ne peuvent dépasser 255 caractères']),
                ]
            ])
            ->add('etatSave', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['value' => 'save',
                    'class' => 'btn btn-primary',
                    'style' => 'width: fit-content'],
            ])
            ->add('etatPost', SubmitType::class, [
                'label' => 'Publier',
                'attr' => ['value' => 'post',
                    'class' => 'btn btn-primary',
                    'style' => 'width: fit-content'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
