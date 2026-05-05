<?php

namespace App\Form\Type;

use App\Entity\Catalog;
use App\Entity\CatalogItem;
use App\Provider\ProductCodeProvider;
use App\Utils\Sort;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
class CatalogItemType extends AbstractType
{
    public function __construct(
        private RouterInterface $router
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $urlAllProducts = $this->router->generate('admin_crud_all_catalog_items_by_catalog_id', ['id' => 0]);
        $urlSortCatalogItems = $this->router->generate('admin_crud_sort_catalog_items');
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
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите slug']),
                    new Length(['max' => 255]),
                    new Regex([
                        'pattern' => '/^[a-z0-9-]+$/',
                        'message' => 'Slug может содержать только маленькие латинские буквы, цифры и дефис.',
                    ]),
                ],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Цена (RUB)',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Укажите цену']),
                    new PositiveOrZero(['message' => 'Цена не может быть отрицательной']),
                    new Range([
                        'max' => 2147483647,
                        'maxMessage' => 'Цена слишком велика для базы данных',
                    ]),
                ],
                'attr' => [
                    'min' => 0,
                    'step' => 1,
                    'placeholder' => '0',
                    'inputmode' => 'numeric',
                    'onkeypress' => "return event.charCode >= 48 && event.charCode <= 57 || event.charCode == 0",
                    'onpaste' => "let paste = (event.clipboardData || window.clipboardData).getData('text'); if(!/^\d+$/.test(paste)) { event.preventDefault(); }",
                ],
            ])
            ->add('catalog', EntityType::class, [
                'class' => Catalog::class,
                'label' => 'Каталог',
                'choice_label' => 'name',
                'placeholder' => 'Выберите каталог...',
                'constraints' => [
                    new NotBlank(['message' => 'Укажите каталог']),
                ],
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'class' => 'js-entity-select',
                ],
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
                    'class' => 'js-position-select'
                ],
                'constraints' => [
                    new PositiveOrZero(),
                    new Range([
                        'max' => 2147483647,
                        'maxMessage' => 'Цена слишком велика для базы данных',
                    ]),
                ],
                'required' => false,
                'help' => Sort::getModal('name', 'img.imgLink', true, $urlSortCatalogItems, $urlAllProducts,'catalog_items'),
                'help_html' => true,
                'row_attr' => [
                    'class' => 'mb-3',
                    'style' => 'display: grid; grid-template-areas: "label label" "image input"; grid-template-columns: 1fr auto; align-items: center; column-gap:15px;',
                ],
                'label_attr' => [
                    'style' => 'grid-area: label;',
                ],
            ])
            ->add('is_published', CheckboxType::class, [
                'label' => 'Опубликован?',
                'required' => false,
                'value' => true
            ])
            ->add('is_new',  CheckboxType::class, [
                'label' => 'Новое?',
                'required' => false,
            ])
            ->add('is_popular',  CheckboxType::class, [
                'label' => 'Популярное?',
                'required' => false,
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
            ->add('sort', HiddenType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'js-hidden-sort',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatalogItem::class,
        ]);
    }
}
