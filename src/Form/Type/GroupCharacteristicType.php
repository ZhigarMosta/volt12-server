<?php

namespace App\Form\Type;

use App\Entity\Catalog;
use App\Entity\CatalogCharacteristic;
use App\Entity\GroupCharacteristic;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupCharacteristicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название',
            ])
            ->add('catalog', EntityType::class, [
                'class' => Catalog::class,
                'label' => 'Категория',
                'choice_label' => 'name',
                'placeholder' => 'Выберите категорию...',
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
            'data_class' => GroupCharacteristic::class,
        ]);
    }
}
