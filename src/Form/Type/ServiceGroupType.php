<?php

namespace App\Form\Type;

use App\Entity\ServiceGroup;
use App\Provider\ProductCodeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ServiceGroupType extends AbstractType
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
            ]);

        $item = $builder->getData();
        if ($item && $item->getImgLink()) {
            $imgHtml = sprintf(
                '<img src="/%s" style="width: 150px; height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">',
                $item->getImgLink()
            );
        } else {
            $imgHtml = '<div style="width: 150px; height: 150px; background: #f8f9fa; border: 1px dashed #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #ccc;">📷</div>';
        }

        $builder->add('file', FileType::class, [
            'label' => 'Изображение (WebP)',
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '20M',
                    'mimeTypes' => ['image/webp'],
                    'mimeTypesMessage' => 'Только WebP',
                ]),
            ],
            'attr' => [
                'accept' => 'image/webp',
            ],
            'help' => $imgHtml,
            'help_html' => true,
            'row_attr' => [
                'class' => 'mb-3',
                'style' => 'display: grid; grid-template-areas: "label label" "image input"; grid-template-columns: auto 1fr; gap: 15px; align-items: center;',
            ],
            'label_attr' => [
                'style' => 'grid-area: label;',
            ],
            'help_attr' => [
                'style' => 'grid-area: image; margin: 0;',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceGroup::class,
        ]);
    }
}
