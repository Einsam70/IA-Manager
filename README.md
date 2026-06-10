# Gestor Local de Imágenes y Fotografías (IA Manager)

[![Licencia: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4)
![Python](https://img.shields.io/badge/Python-3.x-3776AB)
[![Privacidad](https://img.shields.io/badge/privacy-100%25%20Local-orange)](https://developer.mozilla.org/en-US/docs/Web/Privacy)

Una aplicación web local diseñada para **organizar, consultar y etiquetar imágenes generadas con IA y fotografías personales** desde una interfaz sencilla. Todos los archivos, metadatos y datos de la biblioteca permanecen almacenados en tu propio equipo, sin depender de servicios externos.

---

## ✨ Características Principales

* **Bibliotecas Independientes:** Secciones separadas para gestionar imágenes generadas con IA y fotografías personales.
* **Organización por Álbumes:** Agrupa las imágenes en álbumes con nombres, prefijos y numeración secuencial automática.
* **Etiquetas Personalizadas:** Añade, elimina y aplica etiquetas individuales o por lotes para clasificar cómodamente la colección.
* **Búsqueda Avanzada:** Filtra por prompt, modelo, palabras clave, etiquetas y álbumes desde la propia interfaz.
* **Metadatos de Generación:** Extrae automáticamente prompt, prompt negativo, sampler, pasos, CFG, seed, modelo y otros datos incluidos en imágenes compatibles.
* **Metadatos Fotográficos:** Lee información EXIF como cámara, objetivo, distancia focal, exposición, apertura, ISO, fecha y coordenadas GPS.
* **Exportación por Lotes:** Permite seleccionar varias imágenes y exportarlas en un archivo `.zip` junto con sus metadatos.
* **Visor Integrado:** Incluye navegación entre imágenes, ampliación, desplazamiento y consulta detallada de la información almacenada.
* **Modo Oscuro Integrado:** Interfaz adaptable con tema claro y oscuro persistente.
* **Privacidad Local:** La base de datos SQLite, las imágenes y sus metadatos permanecen exclusivamente en el equipo del usuario.

---

## 💾 Almacenamiento y Privacidad

IA Manager utiliza una base de datos local `SQLite` para indexar la colección sin enviar información a internet:

1. **Imágenes Generadas con IA:** Se almacenan organizadas por modelo o álbum, conservando los metadatos técnicos disponibles.
2. **Fotografías Personales:** Se agrupan por álbum y mantienen la información EXIF relevante.
3. **Datos Locales:** La base de datos, las imágenes importadas y los archivos temporales están excluidos del repositorio mediante `.gitignore`.

Cada instalación genera su propia biblioteca al iniciarse por primera vez.

---

## 🚀 Cómo Utilizar

La versión publicada no incluye distribuciones portables de PHP o Python, por lo que ambos deben estar instalados previamente en el sistema.

1. Clona o descarga este repositorio en tu equipo.
2. Instala **PHP 8.0 o superior** y asegúrate de que esté disponible en el `PATH`.
3. Activa las extensiones PHP `pdo_sqlite`, `mbstring`, `zip` y `json`, además de permitir `shell_exec`.
4. Instala **Python 3** y la dependencia necesaria:

   ```powershell
   py -3 -m pip install -r requirements.txt
   ```

   Si no tienes disponible el lanzador `py`, utiliza:

   ```powershell
   python -m pip install -r requirements.txt
   ```

5. Ejecuta `start.bat`.
6. La aplicación se abrirá en `http://127.0.0.1:8000/`.

El script de inicio comprueba los requisitos e inicializa automáticamente la base de datos si todavía no existe.

📂 tu-carpeta-del-proyecto/
* 📁 app/                 ← Código y datos locales de la aplicación
* 📄 start.bat            ← Inicio y comprobación de requisitos
* 📄 requirements.txt     ← Dependencias de Python
* 📄 LICENSE              ← Licencia MIT

---

## 🛠️ Tecnologías Utilizadas

* **PHP 8** (Servidor local, API y gestión de archivos)
* **SQLite** (Base de datos local sin servidor)
* **Python 3** (Procesamiento y extracción de metadatos)
* **Pillow** (Lectura, normalización y conversión de imágenes)
* **HTML5, CSS3 y Vanilla JavaScript** (Interfaz, visor y gestión de la biblioteca)

---

## ⚙️ Configuración Opcional

IA Manager detecta automáticamente Python mediante `py -3`, `python3` o `python`.

Para utilizar un ejecutable concreto, define la variable de entorno `IA_MANAGER_PYTHON` con su ruta completa antes de iniciar la aplicación.

---

## 📄 Licencia

Este proyecto se distribuye bajo la [Licencia MIT](LICENSE). Puedes utilizarlo, copiarlo, modificarlo y redistribuirlo libremente, siempre que se conserve el aviso de copyright y la licencia.
