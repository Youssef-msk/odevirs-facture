<?php

namespace App\Form;

use App\Entity\Customers;
use App\Entity\DeliveryNote;
use App\Entity\Products;
use App\Entity\SalesStatus;
use App\Repository\CustomersRepository;
use App\Repository\ProductsRepository;
use App\Repository\SalesStatusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference')
            ->add('comment',null, [
                'required' => false,
            ])
            ->add('customer',EntityType::class,[
                'class' => Customers::class,
                'choice_label' => 'company',
                'label' => 'company',
                'query_builder' => function(CustomersRepository $cr){
                    return $cr->createQueryBuilder('d')
                        ->orderBy('d.company', 'ASC');
                }
            ])
            ->add('paymentMode', ChoiceType::class, [
                'choices' => [
                    'Espèce'=> '3',
                    'Chèque'=> '1',
                    'Virement'=> '5',
                    'Effet' => '2',
                    'Autre' => '4',
                ],
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DeliveryNote::class,
            'edit' => false,
        ]);
    }
}
