<?php

namespace App\Form;

use App\Entity\Distributor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DistributorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company')
            ->add('ice')
            ->add('email')
            ->add('phone')
            ->add('adresse')
            ->add('ville')
            ->add('zipcode')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Distributor::class,
        ]);
    }
}
