<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Sponsor;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; 
class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Fête' => 'fete',
                    'Conférence' => 'conference',
                    'Formation' => 'formation',
                ],
                'placeholder' => 'Choisir un type', 
            ])            ->add('description')
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('lieu')
            ->add('nbrplaces')
            ->add('sponsor', EntityType::class, [
                'class' => Sponsor::class,
                'choice_label' => 'id_sponsor',
            ])
            // ->add('users', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            //     'multiple' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
