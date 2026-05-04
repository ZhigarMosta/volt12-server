<?php

namespace App\Form\Type;

use App\Entity\Catalog;
use App\Entity\CatalogItem;
use App\Provider\ProductCodeProvider;
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
                    'class' => 'js-catalog-select',
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
                'help' => '<button type="button" style="margin: 0; margin-top: -0.25rem" class="btn btn-done" id="sort-items-btn">Сортировка</button>
                <script>
                    document.getElementById("sort-items-btn").addEventListener("click", function() {
                        const isSortInEditModel = true;
                        const DragItemFlags = {
                            IS_CURRENT:  1 << 0,
                            IS_NEW:      1 << 1,
                        };
                        let positionSelect = document.querySelector(".js-position-select");
                        let modal = null;
                        let editItemId = null;
                        const urlParts = window.location.pathname.split("/");
                        if (urlParts[urlParts.length - 1] === "edit" || urlParts[urlParts.length - 2] === "edit") {
                            editItemId = urlParts[urlParts.length - 2];
                        }
                        const isNew = urlParts[urlParts.length - 1] === "new";
                        const isNewItem = !editItemId || isNaN(parseInt(editItemId));
                        if (!document.getElementById("catalog-item-sort-modal")) {
                            var modalHtml = `<div class="modal fade js-catalog-item-sort-modal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Сортировка продуктов</h5>
                                            <h5 class="modal-title">Изменения применятся после сохранения сущности</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body js-drag-and-drop__content">
                                            <p>helloworld</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                            <button type="button" class="btn btn-primary js-save">Сохранить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                            document.body.insertAdjacentHTML("beforeend", modalHtml);
                        }

                        document.querySelector(".js-save").addEventListener("click", function() {

                            let payload = {
                                items: getResult(),
                                current: null,
                            };

                            if(isSortInEditModel){
                                payload.current = isNew ? -1 : editItemId;
                            }

                            fetch("' . $urlSortCatalogItems . '", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-Requested-With": "XMLHttpRequest"
                                },
                                body: JSON.stringify(payload)
                            }).then(r=> r.ok?r.json():[])
                            .then(data => {
                                if(isSortInEditModel){
                                    positionSelect.value = data;
                                }
                            })

                            modal.hide();
                        });

                        function getResult() {
                            const list = document.getElementById("sortable-list");
                            const items = list.querySelectorAll(".sort-item");
                            const result = [];
                            items.forEach((el, index) => {
                                result.push({id: parseInt(el.dataset.id) || -1, position: index + 1});
                            });
                            return result;
                        }

                        function initDragAndDrop() {
                            const list = document.getElementById("sortable-list");
                            if (!list) return;

                            let draggedItem = null;
                            let initialMouseY = 0;
                            let lastInsertPosition = null;

                            list.querySelectorAll(".sort-item").forEach(item => {
                                item.setAttribute("draggable", "true");

                                item.addEventListener("dragstart", function(e) {
                                    draggedItem = this;
                                    this.style.opacity = "0.5";
                                    e.dataTransfer.effectAllowed = "move";
                                    e.dataTransfer.setData("text/plain", this.dataset.id);
                                    initialMouseY = e.clientY;
                                });

                                item.addEventListener("dragend", function() {
                                    this.style.opacity = "1";
                                    draggedItem = null;
                                    lastInsertPosition = null;
                                });

                                item.addEventListener("dragover", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();

                                    if (!draggedItem || draggedItem === this) return;

                                    const rect = this.getBoundingClientRect();
                                    const before = e.clientX < rect.left + rect.width / 2;

                                    list.insertBefore(draggedItem, before ? this : this.nextElementSibling);
                                });

                                item.addEventListener("drop", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                });
                            });

                            list.addEventListener("dragover", function(e) {
                                e.preventDefault();
                            });

                            list.addEventListener("drop", function(e) {
                                e.preventDefault();
                            });
                        }

                        function DrowDragItem(item,index,flags){
                             let current = ``;
                             let currentColor = ``;
                             if((flags & DragItemFlags.IS_CURRENT) !== 0){
                                 current = `<p style="color: #e83e8c; margin:0;">Текущий</p>`;
                                 currentColor = `border: 2px solid #e83e8c;`;
                             }
                             if((flags & DragItemFlags.IS_NEW) !== 0){
                                 current = `<p style="color: #e83e8c; margin:0;">Новый в каталоге</p>`;
                             }

                            return `<div class="sort-item card mb-2 p-2" style="display:flex;gap:4px; flex-direction:column; justify-content:space-between; height:80px; width:200px; user-select: none; ${currentColor} opacity: 0.7; cursor: grab;" data-id="${item?item.id:editItemId}" data-position="${(index + 1) ?? "new"}">
                                        ${current}
                                        <div style="display:flex; gap:4px; align-items:center; text-align:center; height: 40px;">
                                            ${ item && item.image ? `<img src="/${item.image.imgLink}" style="user-select: none; width: 40px; object-fit: cover;">` : ""}
                                            <p style="margin:0;white-space: nowrap; overflow: hidden;text-overflow: ellipsis;">${item ?item.name: ""}</p>
                                        </div>
                                    </div>`;
                        }

                        modal = new bootstrap.Modal(document.querySelector(".js-catalog-item-sort-modal"));
                        const catalogId = document.querySelector(".js-catalog-select").value;
                        let dragAndDropContent = document.querySelector(".js-drag-and-drop__content");
                        if(!catalogId){
                            dragAndDropContent.innerHTML = "<p>Сортировка по позиции зависит от каталога, выберите каталог и попробуйте ещё раз</p>";
                            modal.show();
                        } else {
                            let url = "' . $urlAllProducts . '".replace("/0", "/" + catalogId);
                            fetch(url)
                                .then(r=> r.ok?r.json():[])
                                .then(data => {
                                    let itemsHtml = "";
                                    let currentItemAdded = false;
                                    data.items.forEach((item, index) => {
                                        if (!isNewItem && item.id === parseInt(editItemId) && isSortInEditModel) {
                                            currentItemAdded = true;
                                            itemsHtml += DrowDragItem(item,index,DragItemFlags.IS_CURRENT);
                                        } else if (item.id !== parseInt(editItemId)) {
                                            itemsHtml += DrowDragItem(item,index,0);
                                        }
                                    });
                                    if (((!isNewItem && !currentItemAdded) || isNew)&& isSortInEditModel) {
                                        itemsHtml += DrowDragItem(null,null,DragItemFlags.IS_CURRENT|DragItemFlags.IS_NEW);
                                    }
                                    dragAndDropContent.innerHTML = `<div id="sortable-list" style="display: flex; flex-wrap:wrap; gap: 10px; transition: all 0.2s ease;">${itemsHtml}</div>`;
                                    initDragAndDrop();
                                    modal.show();
                                });
                        }
                    });
                </script>',
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
