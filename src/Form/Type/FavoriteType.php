<?php

namespace App\Form\Type;

use App\Entity\CatalogItem;
use App\Entity\Favorite;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FavoriteType extends AbstractType
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Favorite::class]);
    }
}
