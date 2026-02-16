<?php

namespace App\Form\Type;

use App\Entity\Catalog;
use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogGroup;
use App\Entity\CatalogGroupCharacteristic;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CatalogGroupCharacteristicType extends AbstractType
{
    public function __construct(
        private RouterInterface $router
    )
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $urlGroupByChar = $this->router->generate('admin_crud_groups_by_characteristic', ['id' => 0]);
        $urlCharsByGroup = $this->router->generate('admin_crud_catalog_characteristics_by_group', ['id' => 0]);
        $urlAllGroup = $this->router->generate('admin_crud_all_catalog_group');
        $urlAllCategoryCharacteristic = $this->router->generate('admin_crud_all_catalog_characteristic');
        $urlCheckCatalogMatch = $this->router->generate('admin_crud_check_catalog_match_between_catalog_group_and_catalog_characteristic');
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
        ])->add('catalogGroup', EntityType::class, [
                'class' => CatalogGroup::class,
                'label' => 'Группа',
                'choice_label' => 'name',
                'placeholder' => 'Выберите группу...',
                'constraints' => [new NotBlank(['message' => 'Выберите группу'])],
                'attr' => [
                    'class' => 'js-group-select',
                    'data-url' => $urlCharsByGroup,
                    'data-url_all' => $urlAllCategoryCharacteristic,
                    'data-url_check' => $urlCheckCatalogMatch,
                ],
            ])
            ->add('catalogCharacteristic', EntityType::class, [
                'class' => CatalogCharacteristic::class,
                'label' => 'Характеристика',
                'choice_label' => 'name',
                'placeholder' => 'Выберите характеристику...',
                'constraints' => [new NotBlank(['message' => 'Выберите характеристику'])],
                'attr' => [
                    'class' => 'js-char-select',
                    'data-url' => $urlGroupByChar,
                    'data-url_all' => $urlAllGroup,
                    'data-url_check' => $urlCheckCatalogMatch,
                ],
                'help' => '
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let groupSelect = document.querySelector(".js-group-select");
                    let charSelect = document.querySelector(".js-char-select");
                    let infoBlock = document.querySelector(".js-info-block");
                    const preselectGroup = "Выберите продукт...";
                    const preselectChar = "Выберите характеристику...";
                    if (!groupSelect || !charSelect) return;

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
                        if (!groupSelect.value && !charSelect.value) {
                            if (select.classList.contains("js-char-select")) {
                                updateSelect(groupSelect, "' . $urlAllGroup . '", preselectGroup);
                            }
                            else {
                                updateSelect(charSelect, "' . $urlAllCategoryCharacteristic . '", preselectChar);
                            }
                        }
                    }


                    groupSelect.addEventListener("change", function() {
                        change(this, charSelect, preselectChar)
                    });

                    charSelect.addEventListener("change", function() {
                        change(this, groupSelect, preselectGroup)
                    });
                });
                </script>',
                'help_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatalogGroupCharacteristic::class,
        ]);
    }
}
