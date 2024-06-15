<?php

namespace App\Form;

use App\Entity\Distributor;
use App\Entity\Products;
use App\Repository\DistributorRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductsType extends AbstractType
{
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('nameCommerciale')
            ->add('price',NumberType::class, options:[
                'label' => 'Prix'
            ])
            ->add('priceReduced',NumberType::class, options:[
                'label' => 'Prix'
            ])
            ->add('priceRevient',NumberType::class, options:[
                'label' => 'Prix revient'
            ])
            ->add('rate',NumberType::class, options:[
                'label' => 'TVA'
            ])
            ->add('rateType', ChoiceType::class, [])
            ->add('priceHt',NumberType::class, options:[
                'label' => 'Prix HT'
            ])
            ->add('quantity')
            ->add('brand')
            ->add('ref')
            ->add('distributor', EntityType::class, [
                'class' => Distributor::class,
                'choice_label' => 'company',
                'label' => 'company',
                'query_builder' => function(DistributorRepository $cr){
                    return $cr->createQueryBuilder('d')
                        ->orderBy('d.company', 'ASC');
                }
            ])
            ->add('description', TextareaType::class)
            ->add('imageFile', VichImageType::class, [
                'required' => false
            ]);

        $builder->get('rateType')->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
