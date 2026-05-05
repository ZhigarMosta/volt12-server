<?php
$urlAllProducts = $context['urlAllProducts'] ?? '';
$urlSortCatalogItems = $context['urlSortCatalogItems'] ?? '';
$catalogId = $context['catalog_id'] ?? 0;

$jsLibrary = <<<JS
    document.getElementById("sort-items-btn").addEventListener("click", function() {
        let modal = null;
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
            </div>`;
            document.body.insertAdjacentHTML("beforeend", modalHtml);
        }

        function renderSortItems(items, currentItemId, isNewItem, nameField = "name", imageField = "image") {
            let itemsHtml = "";
            items.forEach((item, index) => {
                const isCurrent = !isNewItem && item && item.id === parseInt(currentItemId);
                const currentColor = isCurrent ? "border: 2px solid #e83e8c;" : "border: 1px solid #ddd;";
                const current = isCurrent ? '<span style="font-weight: bold; color: #e83e8c;">[Текущий]</span>' : "";
                const imgSrc = item && item[imageField] ? item[imageField].imgLink : "";
                const name = item ? item[nameField] : "Новый/редактируемый";
                
                itemsHtml += `<div class="sort-item card mb-2 p-2" style="display:flex;gap:4px; flex-direction:column; justify-content:space-between; height:80px; width:200px; user-select: none; ${currentColor} opacity:0.7; cursor: grab;" data-id="${item ? item.id : currentItemId}" data-position="${index + 1}">
                    ${current}
                    <div style="display:flex; gap:4px; align-items:center; text-align:center; height: 40px;">
                        ${imgSrc ? '<img src="/' + imgSrc + '" style="user-select: none; width: 40px; object-fit: cover;">' : ""}
                        <p style="margin:0;white-space: nowrap; overflow: hidden;text-overflow: ellipsis;">${name}</p>
                    </div>
                </div>`;
            });
            return itemsHtml;
        }

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

            list.querySelectorAll(".sort-item").forEach(item => {
                item.setAttribute("draggable", "true");

                item.addEventListener("dragstart", function(e) {
                    draggedItem = this;
                    this.style.opacity = "0.5";
                    e.dataTransfer.effectAllowed = "move";
                    e.dataTransfer.setData("text/plain", this.dataset.id);
                });

                item.addEventListener("dragend", function() {
                    this.style.opacity = "1";
                    draggedItem = null;
                });

                item.addEventListener("dragover", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.dataTransfer.dropEffect = "move";

                    if (!draggedItem || draggedItem === this) return;

                    const rect = this.getBoundingClientRect();
                    const before = e.clientX < rect.left + rect.width / 2;

                    if (draggedItem !== this) {
                        list.insertBefore(draggedItem, before ? this : this.nextElementSibling);
                    }
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

        document.querySelector(".js-save").addEventListener("click", function() {
            if (this.disabled) return;
            this.disabled = true;

            let payload = {
                items: getResult(),
                current: null,
            };

            const urlParts = window.location.pathname.split("/");
            const isEdit = urlParts[urlParts.length - 1] === "edit" || urlParts[urlParts.length - 2] === "edit";
            const editItemId = isEdit ? (urlParts[urlParts.length - 2] !== "edit" ? urlParts[urlParts.length - 2] : urlParts[urlParts.length - 3]) : null;
            const isNew = !editItemId || isNaN(parseInt(editItemId));

            if (isEdit) {
                payload.current = isNew ? -1 : editItemId;
            }

            fetch("${urlSortCatalogItems}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify(payload)
            }).then(r => r.ok ? r.json() : [])
            .then(data => {
                if (isEdit && data.position) {
                    const positionSelect = document.querySelector("input[name='catalog_item[position]']");
                    if (positionSelect) {
                        positionSelect.value = data.position;
                    }
                }
                modal.hide();
            }).finally(() => {
                this.disabled = false;
            });
        });

        let modal = new bootstrap.Modal(document.querySelector(".js-catalog-item-sort-modal"));
        const catalogId = ${catalogId};
        let dragAndDropContent = document.querySelector(".js-drag-and-drop__content");

        if (!catalogId) {
            dragAndDropContent.innerHTML = "<p>Сортировка по позиции зависит от каталога, выберите каталог и попробуйте ещё раз</p>";
            modal.show();
        } else {
            let url = "${urlAllProducts}".replace("/0", "/" + catalogId);

            fetch(url, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(r => r.ok ? r.json() : [])
            .then(data => {
                if (!data.items || !Array.isArray(data.items)) return;

                const urlParts = window.location.pathname.split("/");
                const isEdit = urlParts[urlParts.length - 1] === "edit" || urlParts[urlParts.length - 2] === "edit";
                const editItemId = isEdit ? (urlParts[urlParts.length - 2] !== "edit" ? urlParts[urlParts.length - 2] : urlParts[urlParts.length - 3]) : null;
                const isNew = !editItemId || isNaN(parseInt(editItemId));

                let itemsHtml = renderSortItems(data.items, editItemId, isNew, "name", "image");

                if (isEdit && !isNew) {
                    const currentItem = data.items.find(item => item.id === parseInt(editItemId));
                    if (!currentItem) {
                        itemsHtml += renderSortItems([null], editItemId, false, "name", "image");
                    }
                }

                dragAndDropContent.innerHTML = `<div id="sortable-list" style="display: flex; flex-wrap: wrap; gap: 10px; transition: all 0.2s ease;">${itemsHtml}</div>`;
                initDragAndDrop();
                modal.show();
            });
        }
    });
JS;

echo <<<HTML
    <button type="button" style="margin: 0; margin-top: -0.25rem;" class="btn btn-done" id="sort-items-btn">Сортировка</button>
    <script>
        {$jsLibrary}
    </script>
HTML;
