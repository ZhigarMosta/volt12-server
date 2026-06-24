<?php

namespace App\Utils;

class Sort
{
    public static array $map = [
        'catalog_items' => [
            'name' => 'Сортировка продуктов',
            'noSelectEntity' => 'Сортировка по позиции зависит от каталога, выберите каталог и попробуйте ещё раз'
        ],
        'catalog_item_images' => [
            'name' => 'Сортировка картинок',
            'noSelectEntity' => 'Сортировка зависит от продукта, выберите продукт и попробуйте ещё раз'
        ],
        'feedback_from_map' => [
            'name' => 'Сортировка отзывов',
            'noSelectEntity' => ''
        ],
        'catalog_groups' => [
            'name' => 'Сортировка групп характеристик',
            'noSelectEntity' => 'Сортировка зависит от каталога, выберите каталог и попробуйте ещё раз'
        ],
        'catalog_characteristics' => [
            'name' => 'Сортировка характеристик (без группы)',
            'noSelectEntity' => 'Сортировка зависит от каталога, выберите каталог и попробуйте ещё раз'
        ],
        'service_groups' => [
            'name' => 'Сортировка групп услуг',
            'noSelectEntity' => ''
        ],
        'services' => [
            'name' => 'Сортировка услуг',
            'noSelectEntity' => 'Сортировка зависит от группы услуг, выбирете группу и попробуйте ещё раз'
        ],
    ];

    public static function getModal(string $pathName, string $pathImg, bool $isSortInEditModel, string $urlSort, string $urlAllEntities, string $modalName, string $btnLabel = 'Сортировка')
    {
        $uuidSortItemsBtn = 'sort-items-btn-' . uniqid();
        $uuidJsSave = 'js-save-' . uniqid();
        $uuidModal = 'entity-sort-modal-' . uniqid();
        $btnStyle = '';
        if ($isSortInEditModel) {
            $btnStyle = 'margin-top: -0.25rem';
        }
        return '<button type="button" style="margin: 0; ' . $btnStyle . '" class="btn btn-done" id='. json_encode($uuidSortItemsBtn) .'>' . htmlspecialchars($btnLabel) . '</button>
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
                        const modalId = '. json_encode($uuidModal) .';
                        document.querySelectorAll(".js-entity-sort-modal").forEach(el => {
                            let oldModal = bootstrap.Modal.getInstance(el);
                            if (oldModal) {
                                oldModal.dispose();
                            }
                            el.remove();
                        });
                        document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
                        document.body.classList.remove("modal-open");
                        document.body.style.removeProperty("padding-right");
                        var modalHtml = `<div class="modal fade js-entity-sort-modal" id="${modalId}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header" style="display:flex;flex-direction:column;align-items:start;">
                                            <h5 class="modal-title">' . self::$map[$modalName]["name"] . '</h5>
                                            <h6 class="modal-title" style="color:#E2000F">Данные сохранятся после нажатия на кнопку Сохранить</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body js-drag-and-drop__content">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                            <button type="button" class="btn btn-primary ' . $uuidJsSave . '">Сохранить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        document.body.insertAdjacentHTML("beforeend", modalHtml);

                         document.querySelector(' . json_encode('.' . $uuidJsSave) . ').addEventListener("click", function() {
                             if(this.disabled){
                                 if (modal) {
                                     modal.hide();
                                 }
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
                             fetch("' . $urlSort . '", {
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
                             }).finally(()=>{
                                 if (modal) {
                                     modal.hide();
                                 }
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
                                 current = `<p style="color: #e83e8c; margin:0;">Новый</p>`;
                             }

                            return `<div class="sort-item card mb-2 p-2" style="display:flex;gap:4px; flex-direction:column; justify-content:space-between; height:80px; width:200px; user-select: none; ${currentColor} opacity: 0.7; cursor: grab;" data-id="${item?item.id:editItemId}" data-position="${(index + 1) ?? "new"}">
                                        ${current}
                                        <div style="display:flex; gap:4px; align-items:center; text-align:center; height: 40px;">
                                            ${ item && getNestedValue(item, imageField) ? `<img src="/${getNestedValue(item, imageField)}" style="user-select: none; width: 40px; object-fit: cover;">` : ""}
                                            <p style="margin:0;white-space: nowrap; overflow: hidden;text-overflow: ellipsis;">${item ? getNestedValue(item, nameField): ""}</p>
                                        </div>
                                    </div>`;
                        }

                        modal = new bootstrap.Modal(document.getElementById(modalId));
                        let entityId = 0;
                        const entitySelect = document.querySelector(".js-entity-select");
                        if(entitySelect) {
                            entityId  = entitySelect.value;
                        }
                        let dragAndDropContent = document.querySelector(".js-drag-and-drop__content");
                        let url = "' . $urlAllEntities . '";
                        const isEntityIdInUrl = url.slice(-2) !== "/0"
                        if(!isEntityIdInUrl && !entityId){
                            dragAndDropContent.innerHTML = "<p>' . self::$map[$modalName]["noSelectEntity"] . '</p>";
                            modal.show();
                        } else {
                            if (url.slice(-2) === "/0") {
                                url = "' . $urlAllEntities . '".replace("/0", "/" + entityId);
                            }

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

    /**
     * Кнопка сортировки с двумя режимами:
     * - если $secondarySelector имеет значение — используется $urlBySecondary (напр. по группе)
     * - иначе если $primarySelector имеет значение — используется $urlByPrimary (напр. по каталогу без группы)
     */
    public static function getModalTwoMode(
        string $pathName,
        string $pathImg,
        bool $isSortInEditModel,
        string $urlSort,
        string $urlByPrimary,
        string $urlBySecondary,
        string $primarySelector,
        string $secondarySelector,
        string $modalName,
        string $btnLabel = 'Сортировка'
    ): string {
        $uuidSortItemsBtn = 'sort-items-btn-' . uniqid();
        $uuidJsSave = 'js-save-' . uniqid();
        $uuidModal = 'entity-sort-modal-' . uniqid();
        $btnStyle = '';
        if ($isSortInEditModel) {
            $btnStyle = 'margin-top: -0.25rem';
        }
        return '<button type="button" style="margin: 0; ' . $btnStyle . '" class="btn btn-done" id=' . json_encode($uuidSortItemsBtn) . '>' . htmlspecialchars($btnLabel) . '</button>
                <script>
                    document.getElementById(' . json_encode($uuidSortItemsBtn) . ').addEventListener("click", function() {
                        const nameField = ' . json_encode($pathName) . ';
                        const imageField = ' . json_encode($pathImg) . ';
                        const isSortInEditModel = ' . json_encode($isSortInEditModel) . ';
                        const DragItemFlags = { IS_CURRENT: 1 << 0, IS_NEW: 1 << 1 };
                        let positionSelect = document.querySelector(".js-position-select");
                        let modal = null;
                        let editItemId = null;
                        const urlParts = window.location.pathname.split("/");
                        if (urlParts[urlParts.length - 1] === "edit" || urlParts[urlParts.length - 2] === "edit") {
                            editItemId = urlParts[urlParts.length - 2];
                        }
                        const isNew = urlParts[urlParts.length - 1] === "new";
                        const isNewItem = !editItemId || isNaN(parseInt(editItemId));
                        const modalId = ' . json_encode($uuidModal) . ';
                        document.querySelectorAll(".js-entity-sort-modal").forEach(el => {
                            let oldModal = bootstrap.Modal.getInstance(el);
                            if (oldModal) oldModal.dispose();
                            el.remove();
                        });
                        document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
                        document.body.classList.remove("modal-open");
                        document.body.style.removeProperty("padding-right");
                        var modalHtml = `<div class="modal fade js-entity-sort-modal" id="${modalId}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header" style="display:flex;flex-direction:column;align-items:start;">
                                            <h5 class="modal-title">' . self::$map[$modalName]["name"] . '</h5>
                                            <h6 class="modal-title" style="color:#E2000F">Данные сохранятся после нажатия на кнопку Сохранить</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body js-drag-and-drop__content"></div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                            <button type="button" class="btn btn-primary ' . $uuidJsSave . '">Сохранить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        document.body.insertAdjacentHTML("beforeend", modalHtml);

                        document.querySelector(' . json_encode('.' . $uuidJsSave) . ').addEventListener("click", function() {
                            if (this.disabled) { if (modal) modal.hide(); return; }
                            this.disabled = true;
                            let payload = { items: getResult(), current: null };
                            if (isSortInEditModel) { payload.current = isNew ? -1 : editItemId; }
                            fetch("' . $urlSort . '", {
                                method: "POST",
                                headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
                                body: JSON.stringify(payload)
                            }).then(r => r.ok ? r.json() : [])
                            .then(data => { if (isSortInEditModel) { positionSelect.value = data; } })
                            .finally(() => { if (modal) modal.hide(); this.disabled = false; });
                        });

                        function getResult() {
                            const list = document.getElementById("sortable-list");
                            return Array.from(list.querySelectorAll(".sort-item")).map((el, index) => ({
                                id: parseInt(el.dataset.id) || -1,
                                position: index + 1
                            }));
                        }

                        function initDragAndDrop() {
                            const list = document.getElementById("sortable-list");
                            if (!list) return;
                            let draggedItem = null;
                            list.querySelectorAll(".sort-item").forEach(item => {
                                item.setAttribute("draggable", "true");
                                item.addEventListener("dragstart", function(e) {
                                    draggedItem = this; this.style.opacity = "0.5";
                                    e.dataTransfer.effectAllowed = "move";
                                    e.dataTransfer.setData("text/plain", this.dataset.id);
                                });
                                item.addEventListener("dragend", function() { this.style.opacity = "1"; draggedItem = null; });
                                item.addEventListener("dragover", function(e) {
                                    e.preventDefault(); e.stopPropagation();
                                    if (!draggedItem || draggedItem === this) return;
                                    const rect = this.getBoundingClientRect();
                                    const before = e.clientX < rect.left + rect.width / 2;
                                    list.insertBefore(draggedItem, before ? this : this.nextElementSibling);
                                });
                                item.addEventListener("drop", function(e) { e.preventDefault(); e.stopPropagation(); });
                            });
                            list.addEventListener("dragover", e => e.preventDefault());
                            list.addEventListener("drop", e => e.preventDefault());
                        }

                        function getNestedValue(obj, path) {
                            return path.split(".").reduce((cur, key) => cur?.[key], obj);
                        }

                        function DrowDragItem(item, index, flags) {
                            let current = ``, currentColor = ``;
                            if ((flags & DragItemFlags.IS_CURRENT) !== 0) {
                                current = `<p style="color: #e83e8c; margin:0;">Текущий</p>`;
                                currentColor = `border: 2px solid #e83e8c;`;
                            }
                            if ((flags & DragItemFlags.IS_NEW) !== 0) {
                                current = `<p style="color: #e83e8c; margin:0;">Новый</p>`;
                            }
                            return `<div class="sort-item card mb-2 p-2" style="display:flex;gap:4px;flex-direction:column;justify-content:space-between;height:80px;width:200px;user-select:none;${currentColor}opacity:0.7;cursor:grab;" data-id="${item ? item.id : editItemId}" data-position="${(index + 1) ?? "new"}">
                                        ${current}
                                        <div style="display:flex;gap:4px;align-items:center;text-align:center;height:40px;">
                                            ${item && getNestedValue(item, imageField) ? `<img src="/${getNestedValue(item, imageField)}" style="user-select:none;width:40px;object-fit:cover;">` : ""}
                                            <p style="margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item ? getNestedValue(item, nameField) : ""}</p>
                                        </div>
                                    </div>`;
                        }

                        modal = new bootstrap.Modal(document.getElementById(modalId));
                        let dragAndDropContent = document.querySelector(".js-drag-and-drop__content");

                        const secondaryEl = document.querySelector(' . json_encode($secondarySelector) . ');
                        const primaryEl   = document.querySelector(' . json_encode($primarySelector) . ');
                        const secondaryId = secondaryEl ? secondaryEl.value : null;
                        const primaryId   = primaryEl   ? primaryEl.value   : null;

                        let url;
                        if (secondaryId) {
                            url = ' . json_encode($urlBySecondary) . '.replace("/0", "/" + secondaryId);
                        } else if (primaryId) {
                            url = ' . json_encode($urlByPrimary) . '.replace("/0", "/" + primaryId);
                        } else {
                            dragAndDropContent.innerHTML = "<p>' . self::$map[$modalName]["noSelectEntity"] . '</p>";
                            modal.show();
                            return;
                        }

                        fetch(url)
                            .then(r => r.ok ? r.json() : [])
                            .then(data => {
                                let itemsHtml = "";
                                let currentItemAdded = false;
                                data.items.forEach((item, index) => {
                                    if (!isNewItem && item.id === parseInt(editItemId) && isSortInEditModel) {
                                        currentItemAdded = true;
                                        itemsHtml += DrowDragItem(item, index, DragItemFlags.IS_CURRENT);
                                    } else if (item.id !== parseInt(editItemId)) {
                                        itemsHtml += DrowDragItem(item, index, 0);
                                    }
                                });
                                if (((!isNewItem && !currentItemAdded) || isNew) && isSortInEditModel) {
                                    itemsHtml += DrowDragItem(null, null, DragItemFlags.IS_CURRENT | DragItemFlags.IS_NEW);
                                }
                                dragAndDropContent.innerHTML = `<div id="sortable-list" style="display:flex;flex-wrap:wrap;gap:10px;transition:all 0.2s ease;">${itemsHtml}</div>`;
                                initDragAndDrop();
                                modal.show();
                            });
                    });
                </script>';
    }
}
