<?php

// src/Form/RatingType.php
namespace App\Form;

use App\Entity\Rating;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RatingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', HiddenType::class, [
                'attr' => [
                    'class' => 'rating-value' // We'll use this class for JavaScript
                ]
            ])
            ->add('review', TextareaType::class, [
                'label' => 'Your review (optional)',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'Share your experience...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rating::class,
        ]);
    }
}