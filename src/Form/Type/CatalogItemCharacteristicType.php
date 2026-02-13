<?php

namespace App\Form\Type;

use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogItem;
use App\Entity\CatalogItemCharacteristic;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Routing\RouterInterface;

class CatalogItemCharacteristicType extends AbstractType
{
    public function __construct(
        private EntityManagerInterface $em,
        private RouterInterface        $router
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ajaxUrl = $this->router->generate('admin_crud_catalog_characteristics_by_catalog', ['id' => 0]);
        $builder->add('catalogItem', EntityType::class, [
            'class' => CatalogItem::class,
            'label' => 'Продукт',
            'choice_label' => 'name',
            'placeholder' => 'Выберите продукт...',
            'attr' => [
                'class' => 'js-product-select',
                'data-url' => $ajaxUrl
            ],
            'constraints' => [new NotBlank(['message' => 'Выберите продукт'])],
        ]);

        $formModifier = function (FormInterface $form, ?CatalogItem $product = null) {
            $catalogId = $product?->getCatalog()?->getId();
            $form->add('catalogCharacteristic', EntityType::class, [
                'class' => CatalogCharacteristic::class,
                'label' => 'Характеристика каталога',
                'choice_label' => 'name',
                'placeholder' => 'Выберите Характеристику...',
                'constraints' => [new NotBlank(['message' => 'Выберите характеристику'])],
                'query_builder' => function (EntityRepository $er) use ($catalogId) {
                    $qb = $er->createQueryBuilder('cc');
                    if ($catalogId) {
                        $qb->where('cc.catalog = :catalogId')->setParameter('catalogId', $catalogId);
                    } else {
                        $qb->where('cc.id = 0');
                    }
                    return $qb;
                },

                'help' => '
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const productSelect = document.querySelector(".js-product-select");
                    const charSelect = document.querySelector("[id$=\'_catalogCharacteristic\']");
                    if (!productSelect || !charSelect) return;

                    productSelect.addEventListener("change", function() {
                        const productId = this.value;

                        if (!productId) {
                            updateOptions([]);
                            return;
                        }

                        const url = this.getAttribute("data-url").replace("/0", "/" + productId);
                        fetch(url)
                            .then(response => {
                                if (!response.ok) throw new Error("Network response was not ok");
                                return response.json();
                            })
                            .then(data => {
                                updateOptions(data);
                            })
                            .catch(error => {
                                console.error("Error:", error);
                                charSelect.innerHTML = "<option>Ошибка загрузки</option>";
                            });
                    });

                    function updateOptions(data) {
                        charSelect.innerHTML = "<option value=\"\">Выберите Характеристику...</option>";
                        data.forEach(item => {
                            const option = document.createElement("option");
                            option.value = item.id;
                            option.text = item.name;
                            charSelect.add(option);
                        });
                    }
                });
                </script>',
                'help_html' => true,
            ]);
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $formModifier($event->getForm(), $data ? $data->getCatalogItem() : null);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $productId = $data['catalogItem'] ?? null;
            $product = $productId ? $this->em->getRepository(CatalogItem::class)->find($productId) : null;
            $formModifier($event->getForm(), $product);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatalogItemCharacteristic::class,
        ]);
    }
}
