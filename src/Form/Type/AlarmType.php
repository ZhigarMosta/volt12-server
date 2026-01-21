<?php

namespace App\Form\Type;

use App\Entity\Alarm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
// Если установите sylius/money-bundle, можно вернуть MoneyType:
// use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AlarmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Название'])
            // Временно IntegerType (в копейках). Если есть Sylius MoneyBundle, замените на MoneyType.
            ->add('price', IntegerType::class, ['label' => 'Цена (в копейках)']);
            // ->add('price', MoneyType::class, ['label' => 'Цена']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Alarm::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_alarm';
    }
}
