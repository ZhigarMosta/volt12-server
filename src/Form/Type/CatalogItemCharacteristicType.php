<?php

namespace App\Form\Type;

use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogItem;
use App\Entity\CatalogItemCharacteristic;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CatalogItemCharacteristicType extends AbstractType
{
    public function __construct(
        private RouterInterface $router
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $urlProductsByChar = $this->router->generate('admin_crud_products_by_characteristic', ['id' => 0]);
        $urlCharsByProduct = $this->router->generate('admin_crud_catalog_characteristics_by_catalog', ['id' => 0]);
        $urlAllProducts = $this->router->generate('admin_crud_all_products');
        $urlAllCategoryCharacteristic = $this->router->generate('admin_crud_all_catalog_characteristic');
        $urlCheckCatalogMatch = $this->router->generate('admin_crud_check_catalog_match');
        $builder->add('info_block', TextType::class, [
            'mapped' => false,
            'required' => false,
            'disabled' => true,
            'label' => false,
            'data' => '',
            'attr' => [
                'class' => 'ui warning message js-info-block',
                'readonly' => true,
                'style' => 'border:none; background: #fffbe6; display: none;',
            ],
        ]);

        $builder->add('catalogItem', EntityType::class, [
            'class' => CatalogItem::class,
            'placeholder' => 'Выберите продукт...',
            'label' => 'Продукт',
            'choice_label' => 'name',
            'constraints' => [new NotBlank(['message' => 'Выберите продукт'])],
            'attr' => [
                'class' => 'js-product-select',
                'data-url' => $urlCharsByProduct,
                'data-url_all' => $urlAllCategoryCharacteristic,
                'data-url_check' => $urlCheckCatalogMatch,
            ],
        ]);

        $builder->add('catalogCharacteristic', EntityType::class, [
            'class' => CatalogCharacteristic::class,
            'placeholder' => 'Выберите xарактеристику...',
            'label' => 'Характеристика категории',
            'choice_label' => 'name',
            'constraints' => [new NotBlank(['message' => 'Выберите характеристику'])],
            'attr' => [
                'class' => 'js-char-select',
                'data-url' => $urlProductsByChar,
                'data-url_all' => $urlAllProducts,
                'data-url_check' => $urlCheckCatalogMatch,
            ],
            'help' => '
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let productSelect = document.querySelector(".js-product-select");
                    let charSelect = document.querySelector(".js-char-select");
                    let infoBlock = document.querySelector(".js-info-block");
                    if (!productSelect || !charSelect) return;

                    function updateSelect(select, url, placeholder) {
                        setInnerHTML(select, "Загрузка...");
                        fetch(url)
                            .then(r => r.ok ? r.json() : [])
                            .then(data => {
                                setInnerHTML(select, placeholder);
                                if (data.messageInfo) {
                                    infoBlock.value = data.messageInfo;
                                    infoBlock.style.display = "block";
                                }
                                else {
                                    infoBlock.style.display = "none";
                                }
                                data.items.forEach(item => {
                                    const option = new Option(item.name, item.id);
                                    select.add(option);
                                });
                            });
                    }

                    function setInnerHTML(select, placeholder) {
                        select.innerHTML = "<option value=\"\">" + placeholder + "</option>";
                    }

                    function change(self, select, option) {
                        const val = self.value;
                        let url;
                        if (!val) {
                            url = self.dataset.url_all;
                        }
                        else {
                            url = self.dataset.url.replace("/0", "/" + val);
                        }

                        if (select.value && val) {
                            const urlCheck = self.dataset.url_check;

                            const payload = {
                                catalogItemId: productSelect.value,
                                catalogCharacteristicId: charSelect.value
                            };

                            fetch(urlCheck, {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-Requested-With": "XMLHttpRequest"
                                },
                                body: JSON.stringify(payload)
                            })
                            .then(response => {
                                if (!response.ok) {
                                    setInnerHTML(select, "Ошибка, обновитите страницу");
                                    throw new Error("Network response was not ok");
                                }
                                return response.json();
                            })
                            .then(isMatch => {
                                if(!isMatch){
                                    url = self.dataset.url.replace("/0", "/" + val);
                                    updateSelect(select, url, option);
                                }
                            })
                            return;
                        }
                        updateSelect(select, url, option);
                    }


                    productSelect.addEventListener("change", function() {
                        change(this, charSelect, "Выберите характеристику...")
                    });

                    charSelect.addEventListener("change", function() {
                        change(this, productSelect,"Выберите продукт...")
                    });
                });
                </script>',
            'help_html' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CatalogItemCharacteristic::class]);
    }
}
