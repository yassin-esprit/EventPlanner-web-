<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Sponsor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The title cannot be empty'
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 100,
                        'minMessage' => 'Title must be at least {{ limit }} characters',
                        'maxMessage' => 'Title cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Fête' => 'fete',
                    'Conférence' => 'conference',
                    'Formation' => 'formation',
                    'Autre' => 'autre',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select an event type'
                    ]),
                ],
                'placeholder' => 'Select a type',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Description cannot be empty'
                    ]),
                    new Assert\Length([
                        'min' => 20,
                        'minMessage' => 'Description must be at least {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4
                ]
            ])

            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'format' => 'yyyy-MM-dd',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select a date'
                    ]),
                    new Assert\Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'Please enter a valid date'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'Event date must be today or in the future'
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control datepicker',
                    'min' => (new \DateTime())->format('Y-m-d')
                ],
                'invalid_message' => 'Please enter a valid date (YYYY-MM-DD)'
            ]) 
            ->add('lieu', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Location cannot be empty'
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 100,
                        'minMessage' => 'Location must be at least {{ limit }} characters',
                        'maxMessage' => 'Location cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('nbrplaces', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Number of places cannot be empty'
                    ]),
                    new Assert\Positive([
                        'message' => 'Number must be positive'
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => 1000,
                        'message' => 'Maximum {{ compared_value }} places allowed'
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('sponsor', EntityType::class, [
                'class' => Sponsor::class,
                'choice_label' => 'id_sponsor',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select a sponsor'
                    ]),
                ],
                'placeholder' => 'Select a sponsor',
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
            'attr' => ['novalidate' => 'novalidate'], // Disable HTML5 validation
        ]);
    }
}