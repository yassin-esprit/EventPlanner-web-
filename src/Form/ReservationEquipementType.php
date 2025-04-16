<?php

namespace App\Form;

use App\Entity\Equipement;
use App\Entity\Evenement;
use App\Entity\ReservationEquipement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReservationEquipementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_reservation', null, [
                'widget' => 'single_text',
                'label' => 'Date de réservation'
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Confirmé' => 'confirme',
                    'Annulé' => 'annule',
                    'En attente' => 'attente',
                ],
                'placeholder' => 'Choisissez un statut',
            ])
            ->add('equipement', EntityType::class, [
                'class' => Equipement::class,
                'choice_label' => function (Equipement $equipement) {
                    return $equipement->getNom() . ' (#' . $equipement->getId() . ')';
                },
                'label' => 'Équipement',
                'placeholder' => 'Sélectionnez un équipement',
                'constraints' => [
                    new NotNull([
                        'message' => 'Veuillez sélectionner un équipement'
                    ])
                ],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('e')
                    ->where('e.statut = :val')
                    ->setParameter('val', 'disponible');
                }
            ])
            ->add('evenement', EntityType::class, [
                'class' => Evenement::class,
                'choice_label' => function (Evenement $evenement) {
                    return $evenement->getTitre() . ' (#' . $evenement->getId() . ')';
                },
                'label' => 'Événement',
                'placeholder' => 'Sélectionnez un événement',
                'required' => false,
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('e')
                        ->orderBy('e.titre', 'ASC');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationEquipement::class,
        ]);
    }
}