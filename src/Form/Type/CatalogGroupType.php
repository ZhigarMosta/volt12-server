<?php

namespace App\Form\Type;

use App\Entity\Catalog;
use App\Entity\CatalogGroup;
use App\Utils\Sort;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class CatalogGroupType extends AbstractType
{
    public function __construct(
        private RouterInterface $router,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $urlAllGroups = $this->router->generate('admin_crud_all_catalog_groups_by_catalog_id', ['id' => 0]);
        $urlSortGroups = $this->router->generate('admin_crud_sort_catalog_groups');

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
                    'onkeypress' => "return event.charCode >= 48 && event.charCode <= 57 || event.charCode == 0",
                    'onpaste' => "let paste = (event.clipboardData || window.clipboardData).getData('text'); if(!/^\d+$/.test(paste)) { event.preventDefault(); }",
                    'class' => 'js-position-select',
                ],
                'help' => Sort::getModal('name', 'img.imgLink', true, $urlSortGroups, $urlAllGroups, 'catalog_groups', 'Сортировка групп'),
                'help_html' => true,
                'row_attr' => [
                    'class' => 'mb-3',
                    'style' => 'display: grid; grid-template-areas: "label label" "image input"; grid-template-columns: 1fr auto; align-items: center; column-gap:15px;',
                ],
                'label_attr' => [
                    'style' => 'grid-area: label;',
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
                'attr' => [
                    'class' => 'js-entity-select',
                ],
            ])
            ->add('sort', HiddenType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'js-hidden-sort',
                ],
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
