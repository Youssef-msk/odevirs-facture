<?php

namespace App\Form;

use App\Entity\Expenditure;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpenditureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $expenditureType = [
            1 => "",
            2 => "Dépenses de transport",
            3 => "Dépenses d'hébergement",
            4 => "Dépenses de repas et divertissements",
            5 => "Dépenses de fournitures de bureau",
            6 => "Dépenses de services publics",
            7 => "Dépenses de communication",
            8 => "Dépenses de marketing et publicité",
            9 => "Dépenses de voyage",
            10 => "Dépenses d'équipement",
            11 => "Dépenses d'entretien et de réparation",
            12 => "Dépenses de services professionnels",
            13 => "Dépenses d'assurance",
            14 => "Dépenses de taxes et frais de licence",
            15 => "Dépenses de formation et de développement",
            16 => "Dépenses diverses",
            17 => "Autres",
        ];

        $builder
            ->add('date')
            ->add('ref')
            ->add('commentaire', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('invoiceReference')
            ->add('description')
            ->add('otherType')
            ->add('price')
            ->add('hasInvoice')
            ->add('invoiceNumber')
            ->add('type', ChoiceType::class, [
                'choices' => array_flip($expenditureType),
            ])
            ->add('paymentMode', ChoiceType::class, [
                'choices' => [
                    'Espèce'=> '3',
                    'Chèque'=> '1',
                    'Effet' => '2',
                    'Autre' => '4',
                ],
            ])
            ->add('paymentReference',null,[
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expenditure::class,
        ]);
    }
}
