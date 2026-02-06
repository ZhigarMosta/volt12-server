<?php

namespace App\Form\Type;

use App\Entity\Catalog;
use App\Entity\CatalogCharacteristic;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CatalogCharacteristicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название',
            ])
            ->add('catalog', EntityType::class, [
                'class' => Catalog::class,
                'label' => 'Каталог',
                'choice_label' => 'name',
                'placeholder' => 'Выберите каталог...',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatalogCharacteristic::class,
        ]);
    }
}
