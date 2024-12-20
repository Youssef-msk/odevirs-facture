<?php

namespace App\Form;

use App\Entity\Customers;
use App\Entity\Products;
use App\Entity\Sales;
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

class SalesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference')
            ->add('paymentReference')
            ->add('bonCommande')
            ->add('echeance')
            ->add('comment',null, [
                'required' => false,
            ])
            ->add('status',EntityType::class,[
                'class' => SalesStatus::class,
                'choice_label' => 'label',
                'label' => 'company',
                'query_builder' => function(SalesStatusRepository $cr){
                    return $cr->createQueryBuilder('s')
                        ->orderBy('s.label', 'ASC');
                }
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
            'data_class' => Sales::class,
            'edit' => false,
        ]);
    }
}
