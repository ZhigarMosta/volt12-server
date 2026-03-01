<?php

namespace App\Form\Type;

use App\Entity\Catalog;
use App\Entity\CatalogItem;
use App\Entity\CatalogItemImage;
use App\Provider\ProductCodeProvider;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class
CatalogItemImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $item = $builder->getData();
        $constraints = [
            new File([
                'maxSize' => '20M',
                'mimeTypes' => ['image/webp'],
                'mimeTypesMessage' => 'Только WebP',
            ])
        ];
        $isEdit = false;
        if (!$item || !$item->getId()) {
            $isEdit = true;
            $constraints[] = new NotBlank(['message' => 'Выберите изображение']);
        }
        if ($item && $item->getImgLink()) {
            $imgHtml = sprintf(
                '<img src="/%s" style="width: 150px; height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">',
                $item->getImgLink()
            );
        } else {
            $imgHtml = '<div style="width: 150px; height: 150px; background: #f8f9fa; border: 1px dashed #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #ccc;">📷</div>';
        }
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите title']),
                ],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('alt', TextType::class, [
                'label' => 'Alt',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите alt']),
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
                    new NotBlank(['message' => 'Укажите позицию']),
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
            ->add('catalogItem', EntityType::class, [
                'class' => CatalogItem::class,
                'label' => 'Продукт',
                'choice_label' => 'name',
                'placeholder' => 'Выберите продукт...',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите продукт']),
                ],
                'required' => true,
                'empty_data' => '',
            ])->add('file', FileType::class, [
                'label' => 'Изображение (WebP)',
                'required' => $isEdit,
                'constraints' => $constraints,
                'row_attr' => [
                    'class' => 'mb-3',
                    'style' => 'display: grid; grid-template-areas: "label label" "image input"; grid-template-columns: auto 1fr; gap: 15px; align-items: center;',
                ],
                'label_attr' => [
                    'style' => 'grid-area: label;',
                ],
                'attr' => [
                    'style' => 'grid-area: input;',
                    'accept' => 'image/webp',
                ],
                'help' => $imgHtml,
                'help_html' => true,
                'help_attr' => [
                    'style' => 'grid-area: image; margin: 0;',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatalogItemImage::class,
        ]);
    }
}
