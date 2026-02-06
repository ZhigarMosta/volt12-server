<?php

namespace App\Form\Type;

use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogItem;
use App\Entity\CatalogItemCharacteristic;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CatalogItemCharacteristicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('catalogItem', EntityType::class, [
                'class' => CatalogItem::class,
                'label' => 'Продукт',
                'choice_label' => 'name',
                'placeholder' => 'Выберите продукт...',
            ])
            ->add('catalogCharacteristic', EntityType::class, [
                'class' => CatalogCharacteristic::class,
                'label' => 'Характеристика каталога',
                'choice_label' => 'name',
                'placeholder' => 'Выберите Характеристику...',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatalogItemCharacteristic::class,
        ]);
    }
}
