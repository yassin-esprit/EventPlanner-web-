<?php
namespace App\Form;

use App\Entity\Equipement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EquipementType extends AbstractType
{
   // Ajoutez les labels pour plus de clarté
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nom', TextType::class, ['label' => 'Nom'])
        ->add('quantite_totale', IntegerType::class, [
            'label' => 'Quantité totale',
            'attr' => ['min' => 0]
        ])
        ->add('quantite_dispo', IntegerType::class, [
            'label' => 'Quantité disponible',
            'attr' => ['min' => 0]
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => false
        ])
        ->add('statut', ChoiceType::class, [
            'label' => 'Statut',
            'choices' => [
                'Disponible' => 'disponible',
                'Indisponible' => 'indisponible',
                'Maintenance' => 'maintenance'
            ]
        ])
        ;
}
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipement::class,
        ]);
    }
}