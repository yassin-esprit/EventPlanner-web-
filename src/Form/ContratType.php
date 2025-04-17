<?php

namespace App\Form;

use App\Entity\Contrat;
use App\Entity\Sponsor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_contrat', TextType::class, [
                'label' => 'Type de Contrat',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('montant', NumberType::class, [
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'min' => '0',
                ],
                'label' => 'Montant (TND)',
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '4',
                ]
            ])
            ->add('date_debut', null, [
                'label' => 'Date de Début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('date_fin', null, [
                'label' => 'Date de Fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => 'Actif',
                    'Inactif' => 'Inactif',
                ],
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('mode_paiement', TextType::class, [
                'label' => 'Mode de Paiement',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('sponsor', EntityType::class, [
                'class' => Sponsor::class,
                'choice_label' => 'nom',
                'label' => 'Sponsor',
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contrat::class,
        ]);
    }
}
