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
                                        <div class="modal-header es-header">
                                            <div class="es-header-text">
                                                <h5 class="modal-title es-title">' . self::$map[$modalName]["name"] . '</h5>
                                                <p class="es-note">&#9888; Данные сохранятся после нажатия на кнопку «Сохранить»</p>
                                            </div>
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

                        ' . self::sortHelpers() . '

                        ' . self::sortStyles() . '

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
                            dragAndDropContent.innerHTML = "<p class=\"es-empty\">' . self::$map[$modalName]["noSelectEntity"] . '</p>";
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
                                    const listHtml = itemsHtml
                                        ? `<p class="es-hint">&#10303; Перетащите карточки, чтобы изменить порядок</p><div id="sortable-list" class="es-list">${itemsHtml}</div>`
                                        : `<p class="es-empty">Нет элементов для сортировки</p>`;
                                    dragAndDropContent.innerHTML = listHtml;
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
                                        <div class="modal-header es-header">
                                            <div class="es-header-text">
                                                <h5 class="modal-title es-title">' . self::$map[$modalName]["name"] . '</h5>
                                                <p class="es-note">&#9888; Данные сохранятся после нажатия на кнопку «Сохранить»</p>
                                            </div>
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

                        ' . self::sortHelpers() . '

                        ' . self::sortStyles() . '

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
                            dragAndDropContent.innerHTML = "<p class=\"es-empty\">' . self::$map[$modalName]["noSelectEntity"] . '</p>";
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
                                const listHtml = itemsHtml
                                    ? `<p class="es-hint">&#10303; Перетащите карточки, чтобы изменить порядок</p><div id="sortable-list" class="es-list">${itemsHtml}</div>`
                                    : `<p class="es-empty">Нет элементов для сортировки</p>`;
                                dragAndDropContent.innerHTML = listHtml;
                                initDragAndDrop();
                                modal.show();
                            });
                    });
                </script>';
    }

    /**
     * Стили модалки сортировки (инжектятся в <head> один раз).
     */
    private static function sortStyles(): string
    {
        return '
            if (!document.getElementById("entity-sort-styles")) {
                const __sortStyle = document.createElement("style");
                __sortStyle.id = "entity-sort-styles";
                __sortStyle.textContent = `
                    .es-header { align-items:flex-start; gap:12px; }
                    .es-header-text { display:flex; flex-direction:column; gap:8px; min-width:0; }
                    .es-title { font-size:18px; font-weight:700; color:#212529; margin:0; letter-spacing:-.01em; line-height:1.25; }
                    .es-note {
                        display:inline-flex; align-items:center; gap:6px;
                        margin:0; font-size:12.5px; font-weight:500; line-height:1.3;
                        color:#b42318; background:#fff4f3; border:1px solid #fecdca;
                        padding:5px 10px; border-radius:8px;
                    }
                    .es-list { display:flex; flex-direction:column; gap:8px; max-height:60vh; overflow-y:auto; padding:2px 4px; }
                    .es-empty { color:#6c757d; text-align:center; padding:28px 12px; margin:0; }
                    .es-hint { color:#868e96; font-size:12px; margin:0 0 10px; display:flex; align-items:center; gap:6px; }
                    .es-list .sort-item {
                        display:flex; align-items:center; gap:12px;
                        padding:10px 12px; background:#fff;
                        border:1px solid #e9ecef; border-radius:10px;
                        box-shadow:0 1px 2px rgba(0,0,0,.04);
                        cursor:grab; user-select:none;
                        transition:box-shadow .15s ease, border-color .15s ease, background .15s ease;
                    }
                    .es-list .sort-item:hover { border-color:#ced4da; box-shadow:0 3px 10px rgba(0,0,0,.08); }
                    .es-list .sort-item:active { cursor:grabbing; }
                    .es-list .sort-item.dragging { opacity:.45; box-shadow:0 8px 20px rgba(0,0,0,.16); }
                    .es-list .sort-item.es-current { border-color:#e83e8c; background:#fff5fa; }
                    .es-handle { color:#b8bcc2; font-size:18px; line-height:1; flex:0 0 auto; cursor:grab; }
                    .es-pos {
                        flex:0 0 auto; min-width:26px; height:26px; padding:0 6px;
                        display:inline-flex; align-items:center; justify-content:center;
                        background:#f1f3f5; color:#495057; border-radius:13px;
                        font-size:13px; font-weight:600;
                    }
                    .es-list .sort-item.es-current .es-pos { background:#e83e8c; color:#fff; }
                    .es-thumb { width:40px; height:40px; object-fit:cover; border-radius:8px; flex:0 0 auto; background:#f1f3f5; }
                    .es-name { margin:0; flex:1 1 auto; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:14px; color:#212529; }
                    .es-badge {
                        flex:0 0 auto; font-size:11px; font-weight:600; line-height:1.4;
                        color:#e83e8c; background:#fff0f6; border:1px solid #f3c6dc;
                        padding:2px 10px; border-radius:999px; white-space:nowrap;
                    }
                `;
                document.head.appendChild(__sortStyle);
            }
        ';
    }

    /**
     * Общие JS-функции отрисовки и drag&drop сортировки.
     * Используют замыкание вызывающего обработчика: nameField, imageField, editItemId, DragItemFlags.
     */
    private static function sortHelpers(): string
    {
        return '
            function getNestedValue(obj, path) {
                return path.split(".").reduce((cur, key) => cur?.[key], obj);
            }

            function getResult() {
                const list = document.getElementById("sortable-list");
                if (!list) return [];
                return Array.from(list.querySelectorAll(".sort-item")).map((el, index) => ({
                    id: parseInt(el.dataset.id) || -1,
                    position: index + 1
                }));
            }

            function renumber() {
                const list = document.getElementById("sortable-list");
                if (!list) return;
                list.querySelectorAll(".sort-item").forEach((el, i) => {
                    const pos = el.querySelector(".es-pos");
                    if (pos) pos.textContent = i + 1;
                    el.dataset.position = i + 1;
                });
            }

            function getDragAfterElement(container, y) {
                const els = Array.from(container.querySelectorAll(".sort-item:not(.dragging)"));
                let closest = { offset: -Infinity, element: null };
                els.forEach(child => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;
                    if (offset < 0 && offset > closest.offset) {
                        closest = { offset: offset, element: child };
                    }
                });
                return closest.element;
            }

            function initDragAndDrop() {
                const list = document.getElementById("sortable-list");
                if (!list) return;
                renumber();
                let dragged = null;

                list.querySelectorAll(".sort-item").forEach(item => {
                    item.setAttribute("draggable", "true");
                    item.addEventListener("dragstart", function(e) {
                        dragged = this;
                        e.dataTransfer.effectAllowed = "move";
                        e.dataTransfer.setData("text/plain", this.dataset.id);
                        requestAnimationFrame(() => this.classList.add("dragging"));
                    });
                    item.addEventListener("dragend", function() {
                        this.classList.remove("dragging");
                        dragged = null;
                        renumber();
                    });
                });

                list.addEventListener("dragover", function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = "move";
                    if (!dragged) return;
                    const after = getDragAfterElement(list, e.clientY);
                    if (after == null) {
                        if (list.lastElementChild !== dragged) list.appendChild(dragged);
                    } else if (after !== dragged) {
                        list.insertBefore(dragged, after);
                    }
                    renumber();
                });
                list.addEventListener("drop", function(e) { e.preventDefault(); });
            }

            function DrowDragItem(item, index, flags) {
                const isCurrent = (flags & DragItemFlags.IS_CURRENT) !== 0;
                const isNewFlag = (flags & DragItemFlags.IS_NEW) !== 0;
                let badge = "";
                if (isNewFlag) badge = `<span class="es-badge">Новый</span>`;
                else if (isCurrent) badge = `<span class="es-badge">Текущий</span>`;
                const imgSrc = item && getNestedValue(item, imageField);
                const img = imgSrc ? `<img class="es-thumb" src="/${imgSrc}" alt="">` : "";
                const name = item ? (getNestedValue(item, nameField) ?? "") : "";
                return `<div class="sort-item${isCurrent ? " es-current" : ""}" data-id="${item ? item.id : editItemId}">
                            <span class="es-handle">&#10303;</span>
                            <span class="es-pos"></span>
                            ${img}
                            <p class="es-name">${name}</p>
                            ${badge}
                        </div>`;
            }
        ';
    }
}
