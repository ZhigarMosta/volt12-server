<?php

namespace App\Form\Type;

use App\Entity\ServiceGroup;
use App\Provider\ProductCodeProvider;
use App\Utils\Sort;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ServiceGroupType extends AbstractType
{
    public function __construct(
        private RouterInterface $router,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $urlAllServiceGroups = $this->router->generate('admin_crud_all_service_groups');
        $urlSortServiceGroups = $this->router->generate('admin_crud_sort_service_groups');

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
                    'onkeypress' => "return event.charCode >= 48 && event.charCode <= 57 || event.charCode == 0",
                    'onpaste' => "let paste = (event.clipboardData || window.clipboardData).getData('text'); if(!/^\d+$/.test(paste)) { event.preventDefault(); }",
                    'class' => 'js-position-select',
                ],
                'help' => Sort::getModal('name', 'img.imgLink', true, $urlSortServiceGroups, $urlAllServiceGroups, 'service_groups'),
                'help_html' => true,
                'row_attr' => [
                    'class' => 'mb-3',
                    'style' => 'display: grid; grid-template-areas: "label label" "image input"; grid-template-columns: 1fr auto; align-items: center; column-gap:15px;',
                ],
                'label_attr' => [
                    'style' => 'grid-area: label;',
                ],
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
            'data_class' => ServiceGroup::class,
        ]);
    }
}
