<?php

namespace App\Form\Type;

use App\Entity\FeedbackFromMap;
use App\Provider\ProductCodeProvider;
use App\Entity\Catalog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class FeedbackFromMapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user_name', TextType::class, [
                'label' => 'Имя пользователя',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите имя пользователя']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Имя пользователя слишком длинное (макс. {{ limit }} символов)',
                    ]),
                ],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Сообщение',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите сообщение пользователя']),
                    new Length([
                        'max' => 2048,
                        'maxMessage' => 'Имя пользователя слишком длинное (макс. {{ limit }} символов)',
                    ]),
                ],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('feedback_link', TextType::class, [
                'label' => 'Ссылка на отзыв',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите ссылку на источник']),
                    new Length([
                        'max' => 2048,
                        'maxMessage' => 'Имя пользователя слишком длинное (макс. {{ limit }} символов)',
                    ]),
                ],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Позиция',
                'attr' => [
                    'min' => 0,
                    'step' => 1,
                    'placeholder' => '0',
                    'inputmode' => 'numeric',
                    'onkeypress' => "return event.charCode >= 48 && event.charCode <= 57 || event.charCode == 0",
                    'onpaste' => "let paste = (event.clipboardData || window.clipboardData).getData('text'); if(!/^\d+$/.test(paste)) { event.preventDefault(); }",
                ],
                'constraints' => [
                    new PositiveOrZero(),
                    new Range([
                        'max' => 2147483647,
                        'maxMessage' => 'Цена слишком велика для базы данных',
                    ]),
                    new NotBlank(['message' => 'Укажите позицию']),
                ],
                'required' => false,
            ])
            ->add('map', ChoiceType::class, [
                'label' => 'Карта',
                'choices' => FeedbackFromMap::ALL_MAP,
                'placeholder' => 'Выберите карту...',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите название карты']),
                ],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('star_count', ChoiceType::class, [
                'label' => 'Карта',
                'choices' => FeedbackFromMap::COUNT_STAR,
                'placeholder' => 'Выберите оценку в звёздах...',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите оценку в звёздах']),
                ],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('product_code', ChoiceType::class, [
                'label' => 'Код продукта',
                'choices' => ProductCodeProvider::getAllProducts(),
                'placeholder' => 'Выберите тип...',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите код продукта']),
                ],
                'required' => true,
                'empty_data' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FeedbackFromMap::class,
        ]);
    }
}
