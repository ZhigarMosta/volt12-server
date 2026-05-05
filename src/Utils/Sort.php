<?php

namespace App\Utils;

class Sort
{
    public static function getModal(string $pathName, string $pathImg, bool $isSortInEditModel, string $urlSortCatalogItems, string $urlAllProducts, ?int $catalogId = null)
    {
        $uuidSortItemsBtn = 'sort-items-btn-'.uniqid();
        $uuidJsSave = 'js-save-'.uniqid();
        return '<button type="button" style="margin: 0; margin-top: -0.25rem" class="btn btn-done" id='. json_encode($uuidSortItemsBtn) .'>Сортировка</button>
                <script>
                    document.getElementById('. json_encode($uuidSortItemsBtn) .').addEventListener("click", function() {
                        const nameField = '. json_encode($pathName) .';
                        const imageField = '. json_encode($pathImg) .';
                        const isSortInEditModel = '. json_encode($isSortInEditModel) .';
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
                                            <button type="button" class="btn btn-primary ' . $uuidJsSave . '">Сохранить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                            document.body.insertAdjacentHTML("beforeend", modalHtml);
                        }

                        document.querySelector(' . json_encode('.' . $uuidJsSave) . ').addEventListener("click", function() {
                            if(this.disabled){
                                modal.hide();
                                return;
                            }
                            this.disabled = true;

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
                            modal.hide();
                            }).finally(()=>{
                                this.disabled = false;
                            })
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

                        function getNestedValue(obj, path) {
                            return path.split(".").reduce((current, key) => {
                                return current?.[key];
                            }, obj);
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
                                            ${ item && getNestedValue(item, imageField) ? `<img src="/${getNestedValue(item, imageField)}" style="user-select: none; width: 40px; object-fit: cover;">` : ""}
                                            <p style="margin:0;white-space: nowrap; overflow: hidden;text-overflow: ellipsis;">${item ? getNestedValue(item, nameField): ""}</p>
                                        </div>
                                    </div>`;
                        }

                        modal = new bootstrap.Modal(document.querySelector(".js-catalog-item-sort-modal"));
                        let catalogId = 0;
                        if(parseInt('. json_encode($catalogId) .')) {
                             catalogId =  parseInt('. json_encode($catalogId) .');
                        } else {
                            catalogId = document.querySelector(".js-catalog-select").value;
                        }
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
                </script>';
    }
}
