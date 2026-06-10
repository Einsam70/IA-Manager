<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="theme.css">
    <title>Fotografías</title>

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

        .collapseBtn {
            border-color: var(--border, #d9e2ec);
            background: var(--surface, white);
            color: var(--text, #1f2933);
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

        .galleryTools {
            flex-wrap: nowrap;
        }

        .galleryTools select {
            width: auto;
            min-width: 220px;
            flex: 1 1 260px;
        }

        .galleryTools button {
            flex: 0 0 auto;
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

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 18px;
        }

        .photoItem {
            aspect-ratio: 1 / 1;
            padding: 5px;
            border: 1px solid var(--border, #d9e2ec);
            overflow: hidden;
            background: var(--surface-soft, #f8fafc);
            cursor: pointer;
        }

        .photoItem img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: var(--surface-soft, #eee);
        }

        #emptyGallery {
            margin: 18px 0 0;
            color: #52606d;
        }

        #status.error {
            color: #b42318;
            white-space: pre-wrap;
        }

        .photoModal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: none;
            background: var(--modal-backdrop, rgba(51, 23, 42, 0.82));
        }

        .photoModal.isOpen {
            display: block;
        }

        .photoModalDialog {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
            gap: 10px;
            width: 80vw;
            height: 80vh;
            margin: 10vh auto;
            box-sizing: border-box;
            padding: 10px;
            overflow: hidden;
            border: 1px solid var(--border, #d9e2ec);
            background: var(--surface, white);
            color: var(--text, #1f2933);
            box-shadow: 0 18px 48px var(--shadow, rgba(15, 23, 42, 0.18));
        }

        .photoModalStage {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            min-height: 0;
            background: var(--image-stage, #111827);
            overflow: hidden;
            position: relative;
        }

        .photoModalStage img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            transform-origin: center center;
            user-select: none;
            -webkit-user-drag: none;
        }

        .photoModalSide {
            min-height: 0;
            overflow: auto;
            padding: 15px;
            border-left: 1px solid var(--border, #d9e2ec);
            background: var(--surface, white);
            text-align: left;
        }

        .photoDetailGrid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .photoDetailItem {
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border, #d9e2ec);
        }

        .photoDetailItem span {
            display: block;
            margin-bottom: 3px;
            color: var(--muted, #52606d);
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .photoDetailItem strong {
            display: block;
            color: var(--text, #1f2933);
            font-size: 14px;
            font-weight: normal;
            overflow-wrap: anywhere;
        }

        .photoTagList {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .photoTagList span {
            display: inline-block;
            padding: 3px 8px;
            border: 1px solid var(--border, #d9e2ec);
            border-radius: 999px;
            background: var(--surface-soft, #f8fafc);
            color: var(--text, #1f2933);
            font-size: 12px;
            font-weight: normal;
            text-transform: none;
        }

        .photoEditableFields {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .photoEditRow {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 36px;
            align-items: start;
            gap: 6px;
        }

        .photoEditRow input,
        .photoEditRow textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 8px 10px;
            border: 1px solid var(--field-border, #bcccdc);
            border-radius: 4px;
            background: var(--field-bg, white);
            color: var(--text, #1f2933);
            font: inherit;
        }

        .photoEditRow textarea {
            min-height: 110px;
            resize: vertical;
        }

        .saveFieldBtn {
            width: 36px;
            height: 36px;
            padding: 0;
            font-size: 18px;
            line-height: 1;
        }

        #photoSaveStatus {
            min-height: 18px;
            color: var(--muted, #52606d);
            font-size: 12px;
        }

        .photoModalFooter {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 14px;
        }

        #photoModalCounter {
            font-size: 14px;
            font-weight: bold;
        }

        @media (max-width: 840px) {
            .galleryTools {
                flex-wrap: wrap;
            }

            .galleryTools select {
                width: 100%;
                min-width: 0;
                flex-basis: 100%;
            }

            .photoModal {
                overflow: auto;
            }

            .photoModalDialog {
                grid-template-columns: 1fr;
                width: calc(100% - 24px);
                height: auto;
                min-height: calc(100vh - 24px);
                margin: 12px auto;
                overflow: auto;
            }

            .photoModalStage {
                min-height: 280px;
            }

            .photoModalSide {
                border-left: 0;
                border-top: 1px solid var(--border, #d9e2ec);
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Fotografías personales</h1>
        <a href="index.php">Volver al inicio</a>
    </header>

    <div id="photoModal" class="photoModal" onclick="closePhotoModal(event)">
        <div class="photoModalDialog" role="dialog" aria-modal="true" aria-label="Detalle de fotografia">
            <div id="photoModalViewport" class="photoModalStage">
                <img id="photoModalImage" src="" alt="" draggable="false">
            </div>

            <aside class="photoModalSide">
                <div id="photoModalDetails" class="photoDetailGrid"></div>

                <div style="margin-top:12px;">
                    <div style="font-weight:bold; margin-bottom:6px;">Tags</div>
                    <div id="photoModalTags" class="photoTagList"></div>
                </div>

                <div class="photoEditableFields">
                    <div>
                        <label for="photoPlace">Lugar</label>
                        <div class="photoEditRow">
                            <input type="text" id="photoPlace" placeholder="Lugar de la fotografia">
                            <button
                                type="button"
                                class="saveFieldBtn"
                                title="Guardar lugar"
                                aria-label="Guardar lugar"
                                onclick="savePhotoField('place')"
                            >✓</button>
                        </div>
                    </div>

                    <div>
                        <label for="photoComment">Comentarios</label>
                        <div class="photoEditRow">
                            <textarea id="photoComment" placeholder="Recuerdos, personas, detalles o contexto de la fotografia"></textarea>
                            <button
                                type="button"
                                class="saveFieldBtn"
                                title="Guardar comentarios"
                                aria-label="Guardar comentarios"
                                onclick="savePhotoField('user_comment')"
                            >✓</button>
                        </div>
                    </div>

                    <span id="photoSaveStatus"></span>
                </div>

                <div class="photoModalFooter">
                    <div id="photoModalCounter"></div>
                    <button type="button" onclick="closePhotoModal()">Cerrar</button>
                </div>
            </aside>
        </div>
    </div>

    <main>
        <div class="workspaceTools">
            <button class="collapseBtn" id="toggleUploadBtn" onclick="toggleUploadPanel()">Ocultar subida</button>
        </div>

        <section class="panel" id="uploadPanel">
            <h2>Subir álbum</h2>

            <div class="formGrid">
                <div>
                    <label for="albumName">Nombre del álbum</label>
                    <input type="text" id="albumName" placeholder="Vacaciones Roma 2025">
                </div>

                <div>
                    <label for="albumPrefix">Prefijo de archivo</label>
                    <input type="text" id="albumPrefix" placeholder="ROMA" maxlength="6">
                </div>

                <div>
                    <label for="photoTags">Tags del lote</label>
                    <input type="text" id="photoTags" placeholder="familia, vacaciones, roma">
                </div>
            </div>

            <div class="formGrid">
                <div>
                    <label for="photoInput">Carpeta o archivos</label>
                    <input type="file" id="photoInput" webkitdirectory multiple accept="image/*,.tif,.tiff">
                </div>
            </div>

            <div class="actions">
                <button id="uploadBtn" onclick="uploadPhotos()">Subir fotografías</button>
                <progress id="uploadProgress" value="0" max="100"></progress>
                <span id="uploadStatus">0%</span>
            </div>

            <p id="status"></p>
        </section>

        <section class="panel">
            <h2>Galería</h2>

            <div class="galleryTools">
                <select id="albumFilter" onchange="loadGallery(1)">
                    <option value="">Todos los álbumes</option>
                </select>

                <select id="tagFilter" onchange="loadGallery(1)">
                    <option value="">Todos los tags</option>
                </select>

                <button onclick="loadGallery(1)">Actualizar</button>
            </div>

            <div id="gallery" class="gallery"></div>
            <p id="emptyGallery">No hay fotografías personales registradas.</p>

            <div class="actions" style="margin-top:16px;">
                <button id="prevBtn" onclick="prevPage()">Anterior</button>
                <span id="pageInfo">Página 1 de 1</span>
                <button id="nextBtn" onclick="nextPage()">Siguiente</button>
            </div>
        </section>
    </main>

    <script>
        const API_BASE_URL = new URL('../api/photos/', window.location.href).pathname;
        const PUBLIC_BASE_URL = window.location.pathname
            .replace(/\/[^\/]*$/, '')
            .replace(/\/$/, '');

        const pageSize = 20;
        let currentPage = 1;
        let lastTotal = 0;
        let currentPhotoIds = [];
        let currentPhotoModalIndex = -1;
        let currentPhotoId = 0;
        let photoModalZoom = 1;
        let photoModalOffsetX = 0;
        let photoModalOffsetY = 0;
        let isDraggingPhoto = false;
        let photoDragStartX = 0;
        let photoDragStartY = 0;

        function apiUrl(path) {
            return `${API_BASE_URL}${path}`;
        }

        function imageUrl(path) {
            return `${PUBLIC_BASE_URL}${path}`;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDate(value) {
            if (!value) {
                return '';
            }

            return String(value).replace('T', ' ').substring(0, 19);
        }

        function detailItem(label, value) {
            if (value === null || value === undefined || value === '') {
                return '';
            }

            return `
                <div class="photoDetailItem">
                    <span>${escapeHtml(label)}</span>
                    <strong>${escapeHtml(value)}</strong>
                </div>
            `;
        }

        function updatePhotoModalTransform() {
            const image = document.getElementById('photoModalImage');

            image.style.transform =
                `translate(${photoModalOffsetX}px, ${photoModalOffsetY}px) scale(${photoModalZoom})`;
            image.style.cursor = photoModalZoom > 1 ? 'grab' : 'default';
        }

        function resetPhotoModalZoom() {
            photoModalZoom = 1;
            photoModalOffsetX = 0;
            photoModalOffsetY = 0;
            isDraggingPhoto = false;
            updatePhotoModalTransform();
        }

        function clampPhotoModalImage() {
            const viewport = document.getElementById('photoModalViewport');
            const image = document.getElementById('photoModalImage');

            const viewportW = viewport.clientWidth;
            const viewportH = viewport.clientHeight;
            const naturalW = image.naturalWidth;
            const naturalH = image.naturalHeight;

            if (!viewportW || !viewportH || !naturalW || !naturalH) {
                return;
            }

            const imageRatio = naturalW / naturalH;
            const viewportRatio = viewportW / viewportH;
            let visibleW;
            let visibleH;

            if (imageRatio > viewportRatio) {
                visibleW = viewportW;
                visibleH = viewportW / imageRatio;
            } else {
                visibleH = viewportH;
                visibleW = viewportH * imageRatio;
            }

            const scaledW = visibleW * photoModalZoom;
            const scaledH = visibleH * photoModalZoom;
            const maxX = Math.max((scaledW - viewportW) / 2, 0);
            const maxY = Math.max((scaledH - viewportH) / 2, 0);

            photoModalOffsetX = Math.max(-maxX, Math.min(maxX, photoModalOffsetX));
            photoModalOffsetY = Math.max(-maxY, Math.min(maxY, photoModalOffsetY));
        }

        function toggleUploadPanel() {
            const panel = document.getElementById('uploadPanel');
            const button = document.getElementById('toggleUploadBtn');

            panel.hidden = !panel.hidden;
            button.textContent = panel.hidden ? 'Mostrar subida' : 'Ocultar subida';
            localStorage.setItem(
                'photos-upload-panel',
                panel.hidden ? 'hidden' : 'visible'
            );
        }

        function restoreUploadPanel() {
            const panel = document.getElementById('uploadPanel');
            const button = document.getElementById('toggleUploadBtn');

            panel.hidden = localStorage.getItem('photos-upload-panel') === 'hidden';
            button.textContent = panel.hidden ? 'Mostrar subida' : 'Ocultar subida';
        }

        window.onload = async () => {
            restoreUploadPanel();
            await loadAlbums();
            await loadTags();
            await loadGallery(1);
        };

        async function loadAlbums() {
            const res = await fetch(apiUrl('albums.php'));
            const albums = await res.json();
            const select = document.getElementById('albumFilter');
            const current = select.value;

            select.innerHTML = '<option value="">Todos los álbumes</option>';

            albums.forEach(album => {
                const option = document.createElement('option');
                option.value = album.id;
                option.textContent = `${album.display_name} (${album.total})`;
                select.appendChild(option);
            });

            select.value = current;
        }

        async function loadTags() {
            const res = await fetch(apiUrl('tags.php'));
            const tags = await res.json();
            const select = document.getElementById('tagFilter');
            const current = select.value;

            select.innerHTML = '<option value="">Todos los tags</option>';

            tags.forEach(tag => {
                const option = document.createElement('option');
                option.value = tag.id;
                option.textContent = `${tag.name} (${tag.total})`;
                select.appendChild(option);
            });

            select.value = current;
        }

        async function loadGallery(page = 1) {
            currentPage = page;

            const params = new URLSearchParams();
            params.append('page', currentPage);
            params.append('limit', pageSize);

            const albumId = document.getElementById('albumFilter').value;
            const tagId = document.getElementById('tagFilter').value;

            if (albumId) {
                params.append('album_id', albumId);
            }

            if (tagId) {
                params.append('tag_id', tagId);
            }

            const res = await fetch(apiUrl(`list.php?${params.toString()}`));
            const data = await res.json();

            lastTotal = Number(data.total || 0);
            currentPhotoIds = (data.allIds || []).map(Number);
            renderGallery(data.items || []);
            updatePagination();
        }

        function renderGallery(items) {
            const gallery = document.getElementById('gallery');
            const empty = document.getElementById('emptyGallery');

            gallery.innerHTML = '';
            empty.style.display = items.length ? 'none' : 'block';

            items.forEach(photo => {
                const item = document.createElement('div');
                item.className = 'photoItem';
                item.onclick = () => openPhotoModal(photo.id);

                item.innerHTML = `
                    <img
                        src="${imageUrl(photo.path)}"
                        alt="${escapeHtml(photo.filename)}"
                    >
                `;

                gallery.appendChild(item);
            });
        }

        async function openPhotoModal(photoId) {
            const modal = document.getElementById('photoModal');
            const image = document.getElementById('photoModalImage');
            const details = document.getElementById('photoModalDetails');
            const tags = document.getElementById('photoModalTags');
            const place = document.getElementById('photoPlace');
            const comment = document.getElementById('photoComment');
            const saveStatus = document.getElementById('photoSaveStatus');

            currentPhotoId = Number(photoId);
            currentPhotoModalIndex = currentPhotoIds.indexOf(currentPhotoId);

            const position = currentPhotoModalIndex >= 0 ? currentPhotoModalIndex + 1 : 1;
            document.getElementById('photoModalCounter').textContent =
                `${position} / ${currentPhotoIds.length}`;

            image.removeAttribute('src');
            details.innerHTML = '';
            tags.innerHTML = '';
            place.value = '';
            comment.value = '';
            saveStatus.textContent = '';
            modal.classList.add('isOpen');
            resetPhotoModalZoom();

            try {
                const res = await fetch(apiUrl(`image.php?id=${encodeURIComponent(photoId)}`));
                const photo = await res.json();

                if (!res.ok || photo.error) {
                    throw new Error(photo.error || 'No se pudo cargar la fotografia');
                }

                image.src = imageUrl(photo.path);
                image.alt = photo.filename || '';
                image.onload = () => {
                    clampPhotoModalImage();
                    updatePhotoModalTransform();
                };

                details.innerHTML = [
                    detailItem('Album', photo.album_name),
                    detailItem('Fecha original', formatDate(photo.taken_at))
                ].join('');

                tags.innerHTML = (photo.tags || [])
                    .map(tag => `<span>${escapeHtml(tag)}</span>`)
                    .join('');
                place.value = photo.place || '';
                comment.value = photo.user_comment || '';
            } catch (error) {
                details.innerHTML = detailItem('Error', error.message);
            }
        }

        function closePhotoModal(event) {
            if (event && event.target !== document.getElementById('photoModal')) {
                return;
            }

            const modal = document.getElementById('photoModal');
            modal.classList.remove('isOpen');
            resetPhotoModalZoom();
        }

        function openPreviousPhoto() {
            if (currentPhotoModalIndex <= 0) {
                return;
            }

            openPhotoModal(currentPhotoIds[currentPhotoModalIndex - 1]);
        }

        function openNextPhoto() {
            if (currentPhotoModalIndex >= currentPhotoIds.length - 1) {
                return;
            }

            openPhotoModal(currentPhotoIds[currentPhotoModalIndex + 1]);
        }

        async function savePhotoField(field) {
            const status = document.getElementById('photoSaveStatus');
            const formData = new FormData();
            const input = field === 'place'
                ? document.getElementById('photoPlace')
                : document.getElementById('photoComment');

            formData.append('id', currentPhotoId);
            formData.append('field', field);
            formData.append('value', input.value.trim());

            status.textContent = 'Guardando...';

            try {
                const res = await fetch(apiUrl('update_details.php'), {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (!res.ok || data.error) {
                    throw new Error(data.error || 'No se pudieron guardar los cambios');
                }

                status.textContent = 'Cambios guardados.';
            } catch (error) {
                status.textContent = error.message;
            }
        }

        document.addEventListener('keydown', event => {
            const modal = document.getElementById('photoModal');

            if (!modal.classList.contains('isOpen')) {
                return;
            }

            const isEditing = event.target.matches('input, textarea, select');

            if (isEditing && event.key !== 'Escape') {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closePhotoModal();
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                openPreviousPhoto();
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                openNextPhoto();
            }
        });

        document.getElementById('photoModalImage').addEventListener('wheel', event => {
            event.preventDefault();

            const viewport = document.getElementById('photoModalViewport');
            const rect = viewport.getBoundingClientRect();
            const mouseX = event.clientX - rect.left;
            const mouseY = event.clientY - rect.top;
            const prevZoom = photoModalZoom;

            if (event.deltaY < 0) {
                photoModalZoom = Math.min(photoModalZoom + 0.15, 5);
            } else {
                photoModalZoom = Math.max(photoModalZoom - 0.15, 1);
            }

            if (photoModalZoom === 1) {
                photoModalOffsetX = 0;
                photoModalOffsetY = 0;
            } else {
                const zoomFactor = photoModalZoom / prevZoom;
                photoModalOffsetX = mouseX - (mouseX - photoModalOffsetX) * zoomFactor;
                photoModalOffsetY = mouseY - (mouseY - photoModalOffsetY) * zoomFactor;
            }

            clampPhotoModalImage();
            updatePhotoModalTransform();
        });

        document.getElementById('photoModalImage').addEventListener('mousedown', event => {
            if (photoModalZoom <= 1) {
                return;
            }

            event.preventDefault();
            isDraggingPhoto = true;
            photoDragStartX = event.clientX;
            photoDragStartY = event.clientY;
            document.getElementById('photoModalImage').style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', event => {
            if (!isDraggingPhoto) {
                return;
            }

            const dx = event.clientX - photoDragStartX;
            const dy = event.clientY - photoDragStartY;

            photoDragStartX = event.clientX;
            photoDragStartY = event.clientY;
            photoModalOffsetX += dx;
            photoModalOffsetY += dy;

            clampPhotoModalImage();
            updatePhotoModalTransform();
        });

        document.addEventListener('mouseup', () => {
            if (!isDraggingPhoto) {
                return;
            }

            isDraggingPhoto = false;
            updatePhotoModalTransform();
        });

        document.getElementById('photoModalImage').addEventListener('dblclick', () => {
            resetPhotoModalZoom();
        });

        function updatePagination() {
            const totalPages = Math.max(1, Math.ceil(lastTotal / pageSize));

            document.getElementById('prevBtn').disabled = currentPage <= 1;
            document.getElementById('nextBtn').disabled = currentPage >= totalPages;
            document.getElementById('pageInfo').textContent = `Página ${currentPage} de ${totalPages}`;
        }

        function prevPage() {
            if (currentPage > 1) {
                loadGallery(currentPage - 1);
            }
        }

        function nextPage() {
            if (currentPage * pageSize < lastTotal) {
                loadGallery(currentPage + 1);
            }
        }

        async function uploadPhotos() {
            const albumName = document.getElementById('albumName').value.trim();
            const albumPrefix = document.getElementById('albumPrefix').value.trim();
            const tags = document.getElementById('photoTags').value.trim();
            const input = document.getElementById('photoInput');
            const files = Array.from(input.files);
            const button = document.getElementById('uploadBtn');
            const progress = document.getElementById('uploadProgress');
            const status = document.getElementById('uploadStatus');
            const message = document.getElementById('status');

            if (!albumName) {
                alert('Indica un nombre de álbum');
                return;
            }

            if (!files.length) {
                alert('Selecciona al menos una fotografía');
                return;
            }

            button.disabled = true;
            progress.value = 0;
            status.textContent = '0%';
            message.textContent = '';
            message.classList.remove('error');

            const batchSize = 5;
            let processedFiles = 0;
            const skipped = [];

            try {
                for (let i = 0; i < files.length; i += batchSize) {
                    const batch = files.slice(i, i + batchSize);
                    const formData = new FormData();

                    formData.append('albumName', albumName);
                    formData.append('albumPrefix', albumPrefix);
                    formData.append('tags', tags);

                    batch.forEach(file => {
                        formData.append('files[]', file);
                    });

                    const res = await fetch(apiUrl('upload.php'), {
                        method: 'POST',
                        body: formData
                    });

                    const data = await res.json();

                    if (!res.ok || data.error) {
                        throw new Error(data.error || 'No se pudo procesar el lote');
                    }

                    if (Array.isArray(data.skipped)) {
                        skipped.push(...data.skipped);
                    }

                    processedFiles += batch.length;
                    const percent = Math.round((processedFiles / files.length) * 100);
                    progress.value = percent;
                    status.textContent = `${processedFiles}/${files.length} (${percent}%)`;
                }

                if (skipped.length) {
                    const errors = skipped.map(item =>
                        `${item.file || 'archivo'}: ${item.reason || 'sin detalle'}`
                    );

                    message.classList.add('error');
                    message.textContent =
                        `No se pudieron procesar ${skipped.length} archivo(s):\n`
                        + errors.join('\n');
                }

                input.value = '';

                await loadAlbums();
                await loadTags();
                await loadGallery(1);
            } catch (error) {
                message.classList.add('error');
                message.textContent = `No se pudo completar la subida: ${error.message}`;
            } finally {
                button.disabled = false;
            }
        }
    </script>
    <script src="theme.js"></script>
</body>
</html>
