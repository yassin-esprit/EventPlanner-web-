<?php

namespace App\Form;

use App\Entity\CampagnesPublicitaire;
use App\Entity\Publicite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CampagnesPublicitaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_camp', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom de la campagne ne peut pas être vide'
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('budget_camp', NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le budget ne peut pas être vide'
                    ]),
                    new Assert\Positive([
                        'message' => 'Le budget doit être un nombre positif'
                    ]),
                    new Assert\Type([
                        'type' => 'float',
                        'message' => 'Le budget doit être un nombre valide'
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('date_debut_camp', null, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de début est requise'
                    ]),
                    
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('date_fin_camp', null, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de fin est requise'
                    ]),
                
                    new Assert\GreaterThan([
                        'propertyPath' => 'parent.all[date_debut_camp].data',
                        'message' => 'La date de fin doit être postérieure à la date de début'
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('statut_camp', ChoiceType::class, [
                'choices' => [
                    'Suspendue' => 'suspendue',
                    'En cours' => 'en_cours',
                    'Terminée' => 'terminee',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner un statut'
                    ]),
                    new Assert\Choice([
                        'choices' => ['suspendue', 'en_cours', 'terminee'],
                        'message' => 'Choisissez un statut valide'
                    ]),
                ],
                'placeholder' => 'Sélectionnez un statut',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('publicite', EntityType::class, [
                'class' => Publicite::class,
                'choice_label' => 'id_pub',
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Veuillez sélectionner une publicité'
                    ]),
                ],
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CampagnesPublicitaire::class,
            'attr' => ['novalidate' => 'novalidate'], // Disable HTML5 validation
        ]);
    }
}