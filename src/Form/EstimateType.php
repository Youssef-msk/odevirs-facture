<?php

namespace App\Form;

use App\Entity\Customers;
use App\Repository\CustomersRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstimateType extends AbstractType
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
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Est::class,
            'edit' => false,
        ]);
    }
}
