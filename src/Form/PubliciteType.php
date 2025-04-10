<?php

namespace App\Form;

use App\Entity\Publicite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;

class PubliciteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre_pub', TextType::class, [
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
            ->add('type_pub', ChoiceType::class, [
                'choices' => [
                    'Image' => 'image',
                    'Video' => 'video',
                    'Text' => 'texte',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select an advertisement type'
                    ]),
                    new Assert\Choice([
                        'choices' => ['image', 'video', 'texte'],
                        'message' => 'Choose a valid advertisement type'
                    ]),
                ],
                'placeholder' => 'Choose an advertisement type',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('contenu_pub', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Content cannot be empty'
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage' => 'Content must be at least {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('emplacement_pub', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Placement cannot be empty'
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Placement must be at least {{ limit }} characters',
                        'maxMessage' => 'Placement cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('statut_pub', ChoiceType::class, [
                'choices' => [
                    'Active' => 'active',
                    'Inactive' => 'inactif',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select a status'
                    ]),
                    new Assert\Choice([
                        'choices' => ['active', 'inactif'],
                        'message' => 'Choose a valid status'
                    ]),
                ],
                'placeholder' => 'Choose a status',
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Publicite::class,
            'attr' => ['novalidate' => 'novalidate'], // Disable HTML5 validation
        ]);
    }
}