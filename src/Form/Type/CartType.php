<?php

namespace App\Form\Type;

use App\Entity\Cart;
use App\Entity\CatalogItem;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class CartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'placeholder' => 'Выберите пользователя...',
                'label' => 'Пользователь',
                'choice_label' => 'name',
                'constraints' => [new NotBlank(['message' => 'Выберите пользователя'])],
            ])
            ->add('catalogItem', EntityType::class, [
                'class' => CatalogItem::class,
                'placeholder' => 'Выберите товар...',
                'label' => 'Товар',
                'choice_label' => 'name',
                'constraints' => [new NotBlank(['message' => 'Выберите товар'])],
            ])
            ->add('count', IntegerType::class, [
                'label' => 'Количество',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите количество']),
                    new Positive(['message' => 'Количество должно быть больше 0']),
                ],
                'required' => true,
                'empty_data' => '1',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Cart::class]);
    }
}
