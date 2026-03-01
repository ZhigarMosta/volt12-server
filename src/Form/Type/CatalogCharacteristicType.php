<?php

namespace App\Form\Type;

use App\Entity\Catalog;
use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogGroup;
use App\Provider\ProductCodeProvider;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CatalogCharacteristicType extends AbstractType
{
    public function __construct(
        private RouterInterface $router
    )
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $urlGroupByCatalog = $this->router->generate('admin_crud_groups_by_catalog', ['id' => 0]);
        $urlCatalogsByGroup = $this->router->generate('admin_crud_catalogs_by_group', ['id' => 0]);
        $urlAllGroup = $this->router->generate('admin_crud_all_catalog_group');
        $urlAllCatalogs = $this->router->generate('admin_crud_all_catalog');
        $urlCheckCatalogMatch = $this->router->generate('admin_crud_check_catalog_match_between_catalog_group_and_catalog');
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
            ]])
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
            ->add('catalog', EntityType::class, [
                'class' => Catalog::class,
                'label' => 'Каталог',
                'choice_label' => 'name',
                'placeholder' => 'Выберите каталог...',
                'constraints' => [new NotBlank(['message' => 'Выберите каталог'])],
                'attr' => [
                    'class' => 'js-catalog-select',
                    'data-url' => $urlGroupByCatalog,
                    'data-url_all' => $urlAllGroup,
                    'data-url_check' => $urlCheckCatalogMatch,
                ],
            ])
            ->add('catalogGroup', EntityType::class, [
                'class' => CatalogGroup::class,
                'label' => 'Группа',
                'required' => false,
                'choice_label' => 'name',
                'placeholder' => 'Выберите группу...',
                'attr' => [
                    'class' => 'js-group-select',
                    'data-url' => $urlCatalogsByGroup,
                    'data-url_all' => $urlAllCatalogs,
                    'data-url_check' => $urlCheckCatalogMatch,
                ],
                'help' => '
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let catalogSelect = document.querySelector(".js-catalog-select");
                    let groupSelect = document.querySelector(".js-group-select");
                    let infoBlock = document.querySelector(".js-info-block");
                    const preselectCatalog = "Выберите каталог...";
                    const preselectGroup = "Выберите группу...";
                    if (!catalogSelect || !groupSelect) return;

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
                                catalogGroupId: groupSelect.value,
                                catalogId: catalogSelect.value
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
                        if (!catalogSelect.value && !groupSelect.value) {
                            if (select.classList.contains("js-group-select")) {
                                updateSelect(catalogSelect, "' . $urlAllGroup . '", preselectCatalog);
                            }
                            else {
                                updateSelect(groupSelect, "' . $urlAllCatalogs . '", preselectGroup);
                            }
                        }
                    }


                    catalogSelect.addEventListener("change", function() {
                        change(this, groupSelect, preselectGroup)
                    });

                    groupSelect.addEventListener("change", function() {
                        change(this, catalogSelect, preselectCatalog)
                    });
                });
                </script>',
                'help_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatalogCharacteristic::class,
        ]);
    }
}
