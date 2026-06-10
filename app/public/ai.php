<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="theme.css">
    <title>Imágenes generadas IA</title>

    <style>
        :root {
            color-scheme: light;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f7f8fa;
            color: #1f2933;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 24px;
            border-bottom: 1px solid #d9e2ec;
            background: white;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        a {
            color: #1f5f99;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        main {
            width: min(1180px, calc(100% - 32px));
            margin: 28px auto;
        }

        .panel {
            padding: 22px;
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background: white;
        }

        .panel + .panel {
            margin-top: 18px;
        }

        .panel h2 {
            margin: 0 0 18px;
            font-size: 20px;
        }

        .panelHeader {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .panelHeader h2 {
            margin: 0;
        }

        .collapseBtn,
        .themeBtn {
            padding: 6px 10px;
            border-color: #bcccdc;
            background: transparent;
            color: inherit;
        }

        .panel.collapsed .panelBody {
            display: none;
        }

        .workspaceTools {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            padding: 10px;
            border: 1px solid var(--border, #d9e2ec);
            border-radius: 8px;
            background: var(--surface, white);
        }

        .formGrid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        input,
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 8px 10px;
            border: 1px solid #bcccdc;
            border-radius: 4px;
            font: inherit;
            background: white;
        }

        .actions,
        .galleryTools {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        button {
            padding: 8px 14px;
            border: 1px solid #1f5f99;
            border-radius: 4px;
            background: #1f5f99;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        button:disabled {
            border-color: #bcccdc;
            background: #bcccdc;
            cursor: not-allowed;
        }

        progress {
            width: min(360px, 100%);
            height: 18px;
        }

        #status {
            margin: 12px 0 0;
            color: #52606d;
        }

        #result {
            white-space: pre-wrap;
            font-family: Consolas, monospace;
            font-size: 13px;
            line-height: 1.45;
            color: #243b53;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .item {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
            aspect-ratio: 1 / 1;
            overflow: hidden;
        }

        img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: #eee;
        }

        .tagSuggestions {
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            max-height: 180px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ccc;
            z-index: 1000;
            display: none;
        }
        .tagSuggestions div {
            padding: 6px 8px;
            cursor: pointer;
        }

        .tagSuggestions div:hover {
            background: #f0f0f0;
        }
    </style>

</head>
<body>
    <header>
        <h1>Imágenes generadas IA</h1>
        <a href="index.php">Volver al inicio</a>
    </header>

    <div id="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:5000;">

        <div style="background:white; width:80%; height:80%; margin:5% auto; display:flex; padding:10px; gap:10px;">

        <!-- Imagen -->
        <div id="modalImageViewport" style="flex:2; display:flex; justify-content:center; align-items:center; background:#111; overflow:hidden; position:relative;">
            <img id="modalImage" draggable="false" style="width:100%; height:100%; object-fit:contain; user-select:none; -webkit-user-drag:none;">
        </div>

        <!-- Metadatos -->
        <div style="flex:1; padding:15px; overflow:auto; border-left:1px solid #ccc; text-align:left;">
            <div id="metadata" style="
                word-wrap: break-word;
                font-family: monospace;
                font-size: 13px;
                text-align: left !important;
                line-height: 1.4;
            "></div>

            <div style="margin-top:12px;">
                <div style="font-weight:bold; margin-bottom:6px;">Tags</div>

                <div id="imageTags" style="margin-bottom:8px;"></div>

                <input type="text" id="newTag" placeholder="Añadir tag..." autocomplete="off" style="width:100%; box-sizing:border-box;">
                <div id="tagSuggestions" style="border:1px solid #ccc; max-height:120px; overflow:auto; display:none; background:white;"></div>

                <button onclick="saveTag()" style="margin-top:6px;">Guardar tag</button>
            </div>

            <div style="margin-top:10px; display:flex; align-items:center; justify-content:space-between;">
                <button onclick="deleteImage()">Eliminar</button>

                <div id="modalCounter" style="font-size:14px; font-weight:bold;"></div>

                <button onclick="closeModal()">Cerrar</button>
            </div>
        </div>

        </div>

    </div>

    <main>
        <div class="workspaceTools">
            <button class="collapseBtn" id="toggleUploadBtn" onclick="togglePanelByIndex(0, 'toggleUploadBtn', 'subida')">Ocultar subida</button>
            <button class="collapseBtn" id="toggleSearchBtn" onclick="togglePanelByIndex(1, 'toggleSearchBtn', 'búsqueda')">Ocultar búsqueda</button>
        </div>

        <section class="panel" id="uploadPanel">
            <div class="panelHeader">
                <h2>Subir carpeta</h2>
            </div>

            <div class="panelBody">

            <div class="formGrid">
                <div>
                    <label for="aiAlbumSelect">Álbum existente</label>
                    <select id="aiAlbumSelect" onchange="handleAiAlbumChange()">
                        <option value="">Crear nuevo álbum</option>
                    </select>
                </div>

                <div>
                    <label for="aiAlbumName">Nombre del álbum</label>
                    <input type="text" id="aiAlbumName" placeholder="Space Cowboys">
                </div>

                <div>
                    <label for="aiAlbumPrefix">Prefijo de archivo</label>
                    <input type="text" id="aiAlbumPrefix" placeholder="SPACE" maxlength="8">
                </div>

                <div>
                    <label for="uploadTags">Tags del lote</label>
                    <input type="text" id="uploadTags" placeholder="espacio, cowboy, gravity" autocomplete="off">
                </div>                
            </div>

            <div class="formGrid">
                <div style="width:50%">
                    <label for="folderInput">Carpeta o archivos</label>
                    <input type="file" id="folderInput" webkitdirectory multiple>
                </div>                
            </div>
            
            <div class="formGrid">
                <div class="actions">
                    <button onclick="uploadFiles()">Subir</button>
                    <progress id="uploadProgress" value="0" max="100"></progress>
                    <span id="uploadStatus">0%</span>
                </div>
            </div>

            <p id="status"></p>
            </div>

        </section>

        <section class="panel">
            <h2>Búsqueda</h2>

            <div class="formGrid">
                <input type="text" id="searchInput" placeholder="Buscar en prompt...">
                <select id="modelFilter">
                    <option value="">Todos los modelos</option>
                </select>
                <select id="searchAlbumFilter">
                    <option value="">Todos los álbumes</option>
                </select>
                <select id="sortFilter" onchange="searchAll(1)">
                    <option value="id_desc">Más recientes en la app</option>
                    <option value="id_asc">Más antiguas en la app</option>
                    <option value="date_desc">Fecha imagen (más reciente)</option>
                    <option value="date_asc">Fecha imagen (más antigua)</option>
                    <option value="resolution_desc">Mayor resolución</option>
                    <option value="resolution_asc">Menor resolución</option>
                    <option value="size_desc">Mayor tamaño archivo</option>
                    <option value="size_asc">Menor tamaño archivo</option>
                </select>
            </div>

            <div class="formGrid">
                <div style="margin-top:10px;">
                    <input type="text" id="keywordSearch" placeholder="Añadir keyword..." autocomplete="off">
                    <div id="keywordSuggestions" style="border:1px solid #ccc; max-height:150px; overflow:auto; display:none; background:white;"></div>
                    <div id="selectedKeywords" style="margin-top:8px;"></div>
                </div>
                <div style="margin-top:10px;">
                    <input type="text" id="tagSearch" placeholder="Añadir tag..." autocomplete="off">
                    <div id="tagFilterSuggestions" style="border:1px solid #ccc; max-height:150px; overflow:auto; display:none; background:white;"></div>
                    <div id="selectedTags" style="margin-top:8px;"></div>
                </div>
                <div style="margin-top:10px;">
                    <button onclick="searchAll()">Buscar</button>
                    <button onclick="resetSearch()">Reset</button>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Galería</h2>

            <div class="formGrid">
                <div class="grid" id="gallery"></div>
            </div>
        </section>

        <section class="panel">
            <div class="actions" style="margin-top:16px;">
                <button id="prevBtn" onclick="prevPage()">Anterior</button>
                <span id="pageInfo" style="margin: 0 10px;">Página 1 de 1</span>
                <button id="nextBtn" onclick="nextPage()">Siguiente</button>
                <button onclick="exportSelected()">Exportar seleccionadas (ZIP)</button>
                <div style="position:relative; display:inline-block; width:220px;">
                    <input type="text" id="bulkTag" placeholder="Tags para seleccionadas" autocomplete="off" style="width:100%; box-sizing:border-box;">

                    <div id="bulkTagSuggestions"
                        style="
                            position:absolute;
                            bottom:100%;
                            left:0;
                            width:100%;
                            border:1px solid #ccc;
                            max-height:140px;
                            overflow-y:auto;
                            display:none;
                            background:white;
                            z-index:1000;
                            box-sizing:border-box;
                        ">
                    </div>
                </div>
                <button onclick="applyTagToSelected()">Aplicar tag</button>
            </div>            
        </section>

</main>

<script>
    let currentPage = 1;
    const pageSize = 20;
    let lastTotal = 0;
    let availableKeywords = [];
    let availableTags = [];
    let selectedKeywords = [];
    let selectedTags = [];
    let highlightedSuggestion = -1;
    let currentGalleryImages = [];
    let currentModalIndex = -1
    let currentSearchResults = [];
    let modalZoom = 1;
    let modalOffsetX = 0;
    let modalOffsetY = 0;
    let isDraggingImage = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let availableAiAlbums = [];

    const PUBLIC_BASE_URL = window.location.pathname
        .replace(/\/[^\/]*$/, '')
        .replace(/\/$/, '');
    const API_BASE_URL = new URL('../api/', window.location.href).pathname;

    function imageUrl(path) {
        return `${PUBLIC_BASE_URL}${path}`;
    }

    function apiUrl(path) {
        return `${API_BASE_URL}${path}`;
    }

    function togglePanelByIndex(index, buttonId, label) {
        const panels = document.querySelectorAll('main > .panel');
        const panel = panels[index];
        const button = document.getElementById(buttonId);

        if (!panel || !button) return;

        panel.hidden = !panel.hidden;
        button.textContent = panel.hidden ? `Mostrar ${label}` : `Ocultar ${label}`;
        localStorage.setItem(`ia-panel-${index}`, panel.hidden ? 'hidden' : 'visible');
    }

    function restorePanelState(index, buttonId, label) {
        const panels = document.querySelectorAll('main > .panel');
        const panel = panels[index];
        const button = document.getElementById(buttonId);

        if (!panel || !button) return;

        panel.hidden = localStorage.getItem(`ia-panel-${index}`) === 'hidden';
        button.textContent = panel.hidden ? `Mostrar ${label}` : `Ocultar ${label}`;
    }

    window.onload = async () => {
        restorePanelState(0, 'toggleUploadBtn', 'subida');
        restorePanelState(1, 'toggleSearchBtn', 'búsqueda');
        await loadAiAlbums();
        await loadModels();
        await loadKeywords();
        await loadTagFilter();

        searchAll(1);
    };

    async function loadGallery() {
        const res = await fetch(apiUrl(`images.php?page=${currentPage}`));
        const data = await res.json();

        const gallery = document.getElementById('gallery');
        gallery.innerHTML = '';

        data.forEach(img => {
            const div = document.createElement('div');
            div.className = 'item';

            div.innerHTML = `
                <input type="checkbox" value="${img.id}" class="imgCheck"><br>
                <img src="${imageUrl(img.path)}" onclick="openModal(${img.id})" style="cursor:pointer;">
                <small>${img.model}</small>
            `;

            gallery.appendChild(div);
        });
    }

    function nextPage() {
        if (currentPage * pageSize < lastTotal) {
            searchAll(currentPage + 1);
        }
    }

    function prevPage() {
        if (currentPage > 1) {
            searchAll(currentPage - 1);
        }
    }

    function getSelectedIds() {
        const checks = document.querySelectorAll('.imgCheck:checked');
        return Array.from(checks).map(cb => cb.value);
    }

    function exportSelected() {
        const ids = getSelectedIds();

        if (ids.length === 0) {
            alert("Selecciona al menos una imagen");
            return;
        }

        // redirección directa para descarga
        window.location.href = apiUrl(`export.php?ids=${ids.join(',')}`);
    }

    async function applyTagToSelected() {
        const tag = document.getElementById('bulkTag').value.trim().toLowerCase();

        if (!tag) {
            alert("No ha indicado ningún Tag");
            return;
        };

        const selected = Array.from(
            document.querySelectorAll('.imgCheck:checked')
        ).map(cb => cb.value);

        if (!selected.length) {
            alert("Selecciona al menos una imagen");
            return;
        };

        await fetch(apiUrl('bulk_tag.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                ids: selected,
                tag: tag
            })
        });

        document.getElementById('bulkTag').value = '';

        document.querySelectorAll('.imgCheck:checked')
            .forEach(cb => cb.checked = false);

        await loadTagFilter();

        alert('Tag aplicado');
    }

    async function uploadFiles() {
        const input = document.getElementById('folderInput');
        const files = Array.from(input.files);
        const albumSelect = document.getElementById('aiAlbumSelect');
        const albumName = document.getElementById('aiAlbumName').value.trim();
        const albumPrefix = document.getElementById('aiAlbumPrefix').value.trim();
        const uploadTags = document.getElementById('uploadTags').value.trim();

        if (files.length === 0) {
            alert('Selecciona archivos');
            return;
        }

        if (!albumSelect.value && !albumName) {
            alert('Indica un álbum o selecciona uno existente');
            return;
        }

        if (!albumSelect.value && !albumPrefix) {
            alert('Indica un prefijo para el nuevo álbum');
            return;
        }

        const batchSize = 5; // puedes ajustar
        const total = files.length;

        const progress = document.getElementById('uploadProgress');
        const status = document.getElementById('uploadStatus');

        let processed = 0;
        let totalProcessed = 0;
        let activeAlbumId = albumSelect.value;
        const skipped = [];

        for (let i = 0; i < files.length; i += batchSize) {
            const batch = files.slice(i, i + batchSize);

            const formData = new FormData();

            if (activeAlbumId) {
                formData.append('aiAlbumId', activeAlbumId);
            } else {
                formData.append('aiAlbumName', albumName);
                formData.append('aiAlbumPrefix', albumPrefix);
            }

            if (uploadTags) {
                formData.append('uploadTags', uploadTags);
            }

            batch.forEach(file => {
                formData.append('files[]', file);;
            });

            const res = await fetch(apiUrl('upload.php'), {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (!res.ok || data.error) {
                alert(data.error || 'No se pudo completar la subida');
                return;
            }

            totalProcessed += Number(data.processed || 0);

            if (data.album && data.album.id) {
                activeAlbumId = String(data.album.id);
            }

            if (Array.isArray(data.skipped)) {
                skipped.push(...data.skipped);
            }

            processed += batch.length;

            const percent = Math.round((processed / total) * 100);

            progress.value = percent;
            status.textContent = `${processed}/${total} (${percent}%)`;
        }

        await loadAiAlbums();
        await loadModels();
        await loadKeywords();
        await loadTagFilter();
        await searchAll(1);

        input.value = '';
        document.getElementById('uploadTags').value = '';

        status.textContent = `Completado (${total} imágenes)`;
    }

    async function loadAiAlbums() {
        const res = await fetch(apiUrl('ai_albums.php'));
        availableAiAlbums = await res.json();

        const select = document.getElementById('aiAlbumSelect');
        const searchSelect = document.getElementById('searchAlbumFilter');
        const current = select.value;
        const currentSearch = searchSelect ? searchSelect.value : '';

        select.innerHTML = '<option value="">Crear nuevo álbum</option>';

        if (searchSelect) {
            searchSelect.innerHTML = '<option value="">Todos los álbumes</option>';
        }

        availableAiAlbums.forEach(album => {
            const option = document.createElement('option');
            option.value = album.id;
            option.textContent = `${album.display_name} (${album.filename_prefix}, siguiente ${album.next_sequence})`;
            select.appendChild(option);

            if (searchSelect) {
                const searchOption = document.createElement('option');
                searchOption.value = album.id;
                searchOption.textContent = `${album.display_name} (${album.total})`;
                searchSelect.appendChild(searchOption);
            }
        });

        select.value = current;
        if (searchSelect) {
            searchSelect.value = currentSearch;
        }
        handleAiAlbumChange();
    }

    function handleAiAlbumChange() {
        const select = document.getElementById('aiAlbumSelect');
        const nameInput = document.getElementById('aiAlbumName');
        const prefixInput = document.getElementById('aiAlbumPrefix');
        const album = availableAiAlbums.find(item => String(item.id) === String(select.value));

        if (album) {
            nameInput.value = album.display_name;
            prefixInput.value = album.filename_prefix;
            nameInput.disabled = true;
            prefixInput.disabled = true;
        } else {
            nameInput.disabled = false;
            prefixInput.disabled = false;
            nameInput.value = '';
            prefixInput.value = '';
        }
    }

    async function loadModels() {
        const res = await fetch(apiUrl('models.php'));
        const models = await res.json();

        const select = document.getElementById('modelFilter');

        // 🔴 limpiar antes de recargar
        select.innerHTML = '<option value="">Todos los modelos</option>';

        models.forEach(m => {
            const option = document.createElement('option');
            option.value = m;
            option.textContent = m;
            select.appendChild(option);
        });
    }

    async function loadKeywords() {
        const res = await fetch(apiUrl('keywords.php'));
        availableKeywords = await res.json();
    }

    function renderSelectedKeywords() {
        const container = document.getElementById('selectedKeywords');

        container.innerHTML = '';

        selectedKeywords.forEach(word => {
            const tag = document.createElement('span');

            tag.style.display = 'inline-block';
            tag.style.padding = '4px 8px';
            tag.style.margin = '2px';
            tag.style.border = '1px solid #ccc';
            tag.style.cursor = 'pointer';

            tag.textContent = word + ' ✕';

            tag.onclick = () => {
                selectedKeywords = selectedKeywords.filter(k => k !== word);
                renderSelectedKeywords();
            };

            container.appendChild(tag);
        });
    }

    function renderGallery(data) {
        //currentGalleryImages = data;
        currentGalleryImages = currentSearchResults;

        const gallery = document.getElementById('gallery');
        gallery.innerHTML = '';

        data.forEach(img => {
            const div = document.createElement('div');
            div.className = 'item';

            div.innerHTML = `
                <input type="checkbox" value="${img.id}" class="imgCheck"><br>
                <img src="${imageUrl(img.path)}" onclick="openModal(${img.id})" style="cursor:pointer;">
                <small>${img.model}</small>
            `;

            gallery.appendChild(div);
        });
    }

    async function searchAll(page = 1) {
        currentPage = page;

        const q = document.getElementById('searchInput').value.trim();
        const model = document.getElementById('modelFilter').value.trim();
        const albumId = document.getElementById('searchAlbumFilter').value.trim();
        const keywords = selectedKeywords;
        const params = new URLSearchParams();
        const tags = selectedTags;
        const sort = document.getElementById('sortFilter').value;

        if (q) params.append('q', q);
        if (model) params.append('model', model);
        if (albumId) params.append('album_id', albumId);
        if (keywords.length > 0) params.append('keywords', keywords.join(','));

        params.append('page', currentPage);
        params.append('limit', pageSize);
        if (tags.length > 0) {
            params.append('tags', tags.join(','));
        }
        if (sort) params.append('sort', sort);

        const res = await fetch(apiUrl(`search_all.php?${params.toString()}`));
        
        const result = await res.json();

        //currentSearchResults = result.allItems;
        currentSearchResults = result.allIds || [];

        renderGallery(result.items);

        lastTotal = result.total;

        updatePaginationButtons();
    }

    function updatePaginationButtons() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const pageInfo = document.getElementById('pageInfo');

        const totalPages = Math.max(1, Math.ceil(lastTotal / pageSize));

        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = currentPage >= totalPages;

        pageInfo.textContent = `Página ${currentPage} de ${totalPages}`;
    }

    function resetSearch() {
        document.getElementById('searchInput').value = '';
        document.getElementById('modelFilter').value = '';
        document.getElementById('searchAlbumFilter').value = '';
        document.getElementById('sortFilter').value = 'id_desc';

        selectedKeywords = [];
        renderSelectedKeywords();

        selectedTags = [];
        renderSelectedTags();

        searchAll(1);
    }

    function addKeyword(word) {
        if (!selectedKeywords.includes(word)) {
            selectedKeywords.push(word);
        }

        document.getElementById('keywordSearch').value = '';
        document.getElementById('keywordSuggestions').style.display = 'none';
        document.getElementById('keywordSuggestions').innerHTML = '';

        highlightedSuggestion = -1;

        renderSelectedKeywords();
    }

    function updateSuggestionHighlight(items) {
        items.forEach((item, index) => {
            item.style.background = index === highlightedSuggestion ? '#eee' : '';
        });
    }

    document.getElementById('newTag').addEventListener('input', function () {
        const value = this.value.trim().toLowerCase();
        const box = document.getElementById('tagSuggestions');

        if (!value) {
            box.style.display = 'none';
            box.innerHTML = '';
            return;
        }

        const matches = availableTags
            .filter(t => t.toLowerCase().includes(value))
            .slice(0, 8);

        box.innerHTML = '';

        matches.forEach(tag => {
            const div = document.createElement('div');

            div.textContent = tag;
            div.style.padding = '4px 8px';
            div.style.cursor = 'pointer';

            div.onclick = () => {
                document.getElementById('newTag').value = tag;
                box.style.display = 'none';
            };

            box.appendChild(div);
        });

        box.style.display = matches.length ? 'block' : 'none';
    });

    document.getElementById('bulkTag').addEventListener('input', function () {
        const value = this.value.trim().toLowerCase();
        const box = document.getElementById('bulkTagSuggestions');

        if (!value) {
            box.style.display = 'none';
            box.innerHTML = '';
            return;
        }

        const matches = availableTags
            .filter(tag => tag.toLowerCase().includes(value))
            .slice(0, 8);

        box.innerHTML = '';

        matches.forEach(tag => {
            const div = document.createElement('div');

            div.textContent = tag;
            div.style.padding = '4px 8px';
            div.style.cursor = 'pointer';

            div.onclick = () => {
                document.getElementById('bulkTag').value = tag;
                box.style.display = 'none';
            };

            box.appendChild(div);
        });

        box.style.display = matches.length ? 'block' : 'none';
    });

    // cargar al inicio
    loadModels();
    loadKeywords();
    searchAll(1);
</script>

<script>
    let currentImageId = null;

    async function openModal(id) {
        currentImageId = id;
        currentModalIndex = currentSearchResults.indexOf(Number(id));

        const total = currentSearchResults.length;
        const current = currentModalIndex >= 0 ? currentModalIndex + 1 : 1;
        document.getElementById('modalCounter').textContent = `${current} / ${total}`;

        const res = await fetch(apiUrl(`image.php?id=${id}`));
        const data = await res.json();

        document.getElementById('modalImage').src = imageUrl(data.path);
        document.getElementById('modalImage').onload = () => {
            clampModalImage();
            updateModalImageTransform();
        };

        document.getElementById('metadata').innerHTML = `
            <b>Model:</b> ${data.model}<br><br>
            <b>Prompt:</b> ${data.prompt}<br><br>
            <b>Negative:</b> ${data.negative_prompt}<br><br>
            <b>Sampler:</b> ${data.sampler}<br><br>
            <b>Schedule type:</b> ${data.schedule_type}<br><br>
            <b>Steps:</b> ${data.steps}<br><br>
            <b>CFG:</b> ${data.cfg}<br><br>
            <b>Seed:</b> ${data.seed}<br><br>
            `;

        modalZoom = 1;
        modalOffsetX = 0;
        modalOffsetY = 0;

        updateModalImageTransform();

        document.getElementById('modal').style.display = 'block';

        loadImageTags(currentImageId);
        await loadAvailableTags();
    }

    function closeModal() {
        document.getElementById('modal').style.display = 'none';
    }

    async function loadImageTags(imageId) {
        const res = await fetch(apiUrl(`get_tags.php?id=${imageId}`));
        const tags = await res.json();

        const container = document.getElementById('imageTags');
        container.innerHTML = '';

        tags.forEach(tag => {
            const span = document.createElement('span');

            span.textContent = tag + ' ✕';
            span.style.display = 'inline-block';
            span.style.padding = '3px 8px';
            span.style.margin = '2px';
            span.style.border = '1px solid #ccc';
            span.style.cursor = 'pointer';

            span.onclick = async () => {
                await removeTag(tag);
            };

            container.appendChild(span);
        });
    }

    async function saveTag() {
        const input = document.getElementById('newTag');
        const tag = input.value.trim();

        if (!tag || !currentImageId) return;

        await fetch(apiUrl('save_tag.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `image_id=${encodeURIComponent(currentImageId)}&tag=${encodeURIComponent(tag)}`
        });

        input.value = '';

        loadImageTags(currentImageId);
        await loadTagFilter();
    }

    async function removeTag(tag) {
        if (!currentImageId) return;

        await fetch(apiUrl('delete_tag.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `image_id=${encodeURIComponent(currentImageId)}&tag=${encodeURIComponent(tag)}`
        });

        loadImageTags(currentImageId);
    }

    async function loadAvailableTags() {
        const res = await fetch(apiUrl('all_tags.php'));
        availableTags = await res.json();
    }

    async function loadTagFilter() {
        const res = await fetch(apiUrl('all_tags.php'));
        availableTags = await res.json();
    }
    
    function renderSelectedTags() {
        const container = document.getElementById('selectedTags');

        container.innerHTML = '';

        selectedTags.forEach(tag => {
            const span = document.createElement('span');

            span.textContent = tag + ' ✕';
            span.style.display = 'inline-block';
            span.style.padding = '4px 8px';
            span.style.margin = '2px';
            span.style.border = '1px solid #ccc';
            span.style.cursor = 'pointer';

            span.onclick = () => {
                selectedTags = selectedTags.filter(t => t !== tag);
                renderSelectedTags();
            };

            container.appendChild(span);
        });
    }

    async function setupUploadTagAutocomplete() {
        const input = document.getElementById('uploadTag');
        const box = document.getElementById('uploadTagSuggestions');

        if (!input || !box) return;

        input.addEventListener('input', async function () {
            const q = input.value.trim();

            if (!q) {
                box.style.display = 'none';
                box.innerHTML = '';
                return;
            }

            const res = await fetch(apiUrl(`tags.php?q=${encodeURIComponent(q)}`));
            const tags = await res.json();

            box.innerHTML = '';

            if (!tags.length) {
                box.style.display = 'none';
                return;
            }

            tags.forEach(tag => {
                const div = document.createElement('div');
                div.textContent = tag;

                div.onclick = function () {
                    input.value = tag;
                    box.innerHTML = '';
                    box.style.display = 'none';
                };

                box.appendChild(div);
            });

            box.style.display = 'block';
        });

        document.addEventListener('click', function (e) {
            if (!box.contains(e.target) && e.target !== input) {
                box.style.display = 'none';
            }
        });
    }

    function openPreviousImage() {
        if (currentModalIndex <= 0) return;

        currentModalIndex--;
        openModal(currentSearchResults[currentModalIndex]);
    }

    function openNextImage() {
        if (currentModalIndex >= currentSearchResults.length - 1) return;

        currentModalIndex++;
        openModal(currentSearchResults[currentModalIndex]);
    }

    function updateModalImageTransform() {
        const img = document.getElementById('modalImage');

        img.style.transform =
            `translate(${modalOffsetX}px, ${modalOffsetY}px) scale(${modalZoom})`;

        img.style.cursor = modalZoom > 1 ? 'grab' : 'default';
    }

    function clampModalImage() {
        const viewport = document.getElementById('modalImageViewport');
        const img = document.getElementById('modalImage');

        const viewportW = viewport.clientWidth;
        const viewportH = viewport.clientHeight;

        const naturalW = img.naturalWidth;
        const naturalH = img.naturalHeight;

        if (!naturalW || !naturalH) return;

        // tamaño visible real de la imagen con object-fit: contain
        const imageRatio = naturalW / naturalH;
        const viewportRatio = viewportW / viewportH;

        let visibleW, visibleH;

        if (imageRatio > viewportRatio) {
            // limitada por ancho
            visibleW = viewportW;
            visibleH = viewportW / imageRatio;
        } else {
            // limitada por alto
            visibleH = viewportH;
            visibleW = viewportH * imageRatio;
        }

        const scaledW = visibleW * modalZoom;
        const scaledH = visibleH * modalZoom;

        const maxX = Math.max((scaledW - viewportW) / 2, 0);
        const maxY = Math.max((scaledH - viewportH) / 2, 0);

        modalOffsetX = Math.max(-maxX, Math.min(maxX, modalOffsetX));
        modalOffsetY = Math.max(-maxY, Math.min(maxY, modalOffsetY));
    }

</script>

<script>
    async function deleteImage() {
        if (!confirm("¿Eliminar imagen?")) return;

        const formData = new FormData();
        formData.append('id', currentImageId);

        const res = await fetch(apiUrl('delete.php'), {
            method: 'POST',
            body: formData
        });

        const text = await res.text();
        alert(text);

        closeModal();
        searchAll(1);
    }
</script>

<script>
    document.getElementById('modal').addEventListener('click', function(e) {
        if (e.target.id === 'modal') {
            closeModal();
        }
    });

    document.getElementById('keywordSearch').addEventListener('input', function () {
        const value = this.value.trim().toLowerCase();
        const box = document.getElementById('keywordSuggestions');

        highlightedSuggestion = -1;

        if (!value) {
            box.style.display = 'none';
            box.innerHTML = '';
            return;
        }

        const matches = availableKeywords
            .filter(k =>
                k.word.toLowerCase().includes(value) &&
                !selectedKeywords.includes(k.word)
            )
            .slice(0, 12);

        box.innerHTML = '';

        matches.forEach((item, index) => {
            const div = document.createElement('div');

            div.textContent = `${item.word} (${item.total})`;
            div.dataset.word = item.word;
            div.style.padding = '4px 8px';
            div.style.cursor = 'pointer';

            div.onclick = () => addKeyword(item.word);

            box.appendChild(div);
        });

        box.style.display = matches.length ? 'block' : 'none';
    });

    document.getElementById('keywordSearch').addEventListener('keydown', function (e) {
        const box = document.getElementById('keywordSuggestions');
        const items = box.querySelectorAll('div');

        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedSuggestion = Math.min(highlightedSuggestion + 1, items.length - 1);
            updateSuggestionHighlight(items);
        }

        else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedSuggestion = Math.max(highlightedSuggestion - 1, 0);
            updateSuggestionHighlight(items);
        }

        else if (e.key === 'Enter') {
            e.preventDefault();

            if (highlightedSuggestion >= 0) {
                addKeyword(items[highlightedSuggestion].dataset.word);
            }
        }

        else if (e.key === 'Escape') {
            box.style.display = 'none';
            highlightedSuggestion = -1;
        }
    });

    document.getElementById('tagSearch').addEventListener('input', function () {
        const value = this.value.trim().toLowerCase();
        const box = document.getElementById('tagFilterSuggestions');

        if (!value) {
            box.style.display = 'none';
            box.innerHTML = '';
            return;
        }

        const matches = availableTags
            .filter(tag =>
                tag.toLowerCase().includes(value) &&
                !selectedTags.includes(tag)
            )
            .slice(0, 10);

        box.innerHTML = '';

        matches.forEach(tag => {
            const div = document.createElement('div');

            div.textContent = tag;
            div.style.padding = '4px 8px';
            div.style.cursor = 'pointer';

            div.onclick = () => {
                selectedTags.push(tag);

                document.getElementById('tagSearch').value = '';
                box.style.display = 'none';

                renderSelectedTags();
            };

            box.appendChild(div);
        });

        box.style.display = matches.length ? 'block' : 'none';
    });

    document.addEventListener('keydown', function (e) {
        const modal = document.getElementById('modal');

        if (modal.style.display !== 'block') return;

        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            openPreviousImage();
        }

        else if (e.key === 'ArrowRight') {
            e.preventDefault();
            openNextImage();
        }

        else if (e.key === 'Escape') {
            e.preventDefault();
            closeModal();
        }
    });

    document.getElementById('modalImage').addEventListener('wheel', function (e) {
        e.preventDefault();

        const viewport = document.getElementById('modalImageViewport');
        const rect = viewport.getBoundingClientRect();

        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        const prevZoom = modalZoom;

        if (e.deltaY < 0) {
            modalZoom = Math.min(modalZoom + 0.15, 5);
        } else {
            modalZoom = Math.max(modalZoom - 0.15, 1);
        }

        if (modalZoom === 1) {
            modalOffsetX = 0;
            modalOffsetY = 0;
        } else {
            const zoomFactor = modalZoom / prevZoom;

            modalOffsetX = mouseX - (mouseX - modalOffsetX) * zoomFactor;
            modalOffsetY = mouseY - (mouseY - modalOffsetY) * zoomFactor;
        }

        clampModalImage();
        updateModalImageTransform();
    });

    const modalImage = document.getElementById('modalImage');

    modalImage.addEventListener('mousedown', function (e) {
        if (modalZoom <= 1) return;

        e.preventDefault();

        isDraggingImage = true;

        dragStartX = e.clientX;
        dragStartY = e.clientY;

        modalImage.style.cursor = 'grabbing';
    });

    document.addEventListener('mousemove', function (e) {
        if (!isDraggingImage) return;

        const dx = e.clientX - dragStartX;
        const dy = e.clientY - dragStartY;

        dragStartX = e.clientX;
        dragStartY = e.clientY;

        modalOffsetX += dx;
        modalOffsetY += dy;

        clampModalImage();
        updateModalImageTransform();
    });

    document.addEventListener('mouseup', function () {
        if (!isDraggingImage) return;

        isDraggingImage = false;

        updateModalImageTransform();
    });

    document.getElementById('modalImage').addEventListener('dblclick', function () {
        modalZoom = 1;
        modalOffsetX = 0;
        modalOffsetY = 0;

        updateModalImageTransform();
    });

</script>

<script src="theme.js"></script>
</body>
</html>
