<?php

namespace App\Form\Type;

use App\Entity\CatalogItem;
use App\Utils\Sort;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Общая форма табличек «товар — товар». Наследники задают класс сущности,
 * ключ модалки сортировки и роуты CRUD-эндпоинтов сортировки.
 */
abstract class AbstractCatalogItemLinkType extends AbstractType
{
    public function __construct(
        protected RouterInterface $router,
    ) {}

    /** FQCN сущности-связки. */
    abstract protected function getDataClass(): string;

    /** Ключ в Sort::$map. */
    abstract protected function getModalKey(): string;

    /** Имя роута «все связки товара» (с плейсхолдером id). */
    abstract protected function getAllByItemRoute(): string;

    /** Имя роута сохранения сортировки. */
    abstract protected function getSortRoute(): string;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $urlAll = $this->router->generate($this->getAllByItemRoute(), ['id' => 0]);
        $urlSort = $this->router->generate($this->getSortRoute());

        $byName = static fn (EntityRepository $er) => $er->createQueryBuilder('ci')->orderBy('ci.name', 'ASC');

        $builder
            ->add('catalogItem', EntityType::class, [
                'class' => CatalogItem::class,
                'label' => 'Товар (на чьей странице блок)',
                'choice_label' => 'name',
                'placeholder' => 'Выберите товар...',
                'query_builder' => $byName,
                'constraints' => [new NotBlank(['message' => 'Выберите товар'])],
                'required' => true,
                'attr' => [
                    'class' => 'js-entity-select',
                ],
            ])
            ->add('linkedItem', EntityType::class, [
                'class' => CatalogItem::class,
                'label' => 'Связанный товар (что показать в блоке)',
                'choice_label' => 'name',
                'placeholder' => 'Выберите товар...',
                'query_builder' => $byName,
                'constraints' => [new NotBlank(['message' => 'Выберите связанный товар'])],
                'required' => true,
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
                    'class' => 'js-position-select',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Укажите позицию']),
                ],
                'required' => true,
                'empty_data' => '',
                'help' => Sort::getModal('name', 'img.imgLink', true, $urlSort, $urlAll, $this->getModalKey()),
                'help_html' => true,
                'row_attr' => [
                    'class' => 'mb-3',
                    'style' => 'display: grid; grid-template-areas: "label label" "image input"; grid-template-columns: 1fr auto; align-items: center; column-gap:15px;',
                ],
                'label_attr' => [
                    'style' => 'grid-area: label;',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => $this->getDataClass()]);
    }
}
