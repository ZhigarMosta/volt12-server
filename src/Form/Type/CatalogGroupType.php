<?php

namespace App\Form\Type;

use App\Entity\Catalog;
use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogGroup;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class CatalogGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите наименование']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Название слишком длинное (макс. {{ limit }} символов)',
                    ]),
                ],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Позиция',
                'constraints' => [
                    new PositiveOrZero(['message' => 'Позиция не может быть отрицательной']),
                ],
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'min' => 0,
                    'step' => 1,
                    'placeholder' => '0',
                    'inputmode' => 'numeric',
                ],
            ])
            ->add('catalog', EntityType::class, [
                'class' => Catalog::class,
                'label' => 'Категория',
                'choice_label' => 'name',
                'placeholder' => 'Выберите категорию...',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите каталог']),
                ],
                'required' => true,
                'empty_data' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatalogGroup::class,
        ]);
    }
}
