import sys
import os
from PIL import Image
from datetime import datetime

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")

if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

def extract_metadata(img):
    metadata = img.info

    result = {
        "Model": "unknown",
        "Prompt": "",
        "Negative prompt": "",
        "Sampler": "",
        "Schedule type": "",
        "Steps": "",
        "CFG scale": "",
        "Seed": ""
    }

    if "parameters" not in metadata:
        return result

    params = metadata["parameters"]

    # Separar en bloques
    parts = params.split("\n")

    # 1️⃣ Prompt (primera línea)
    if len(parts) > 0:
        result["Prompt"] = parts[0].strip()

    # 2️⃣ Negative prompt
    for part in parts:
        if part.startswith("Negative prompt:"):
            result["Negative prompt"] = part.replace("Negative prompt:", "").strip()

    # 3️⃣ Extraer TODOS los parámetros tipo "clave: valor"
    import re

    # Unir todo excepto prompt inicial
    full_text = " ".join(parts[1:])

    # Buscar pares clave:valor
    matches = re.findall(r'([\w\s]+):\s*([^,]+)', full_text)

    for key, value in matches:
        key = key.strip()
        value = value.strip()

        if key == "Steps":
            result["Steps"] = value

        elif key == "Sampler":
            result["Sampler"] = value

        elif key == "Schedule type":
            result["Schedule type"] = value

        elif key == "CFG scale":
            result["CFG scale"] = value

        elif key == "Seed":
            result["Seed"] = value

        elif key == "Model":
            result["Model"] = value

    # 4️⃣ Fallback si existe como campo directo
    if "Model" in metadata and metadata["Model"]:
        result["Model"] = metadata["Model"]

    return result

import re

def extract_keywords(prompt):
    prompt = prompt.lower().strip()

    # 🔥 normalizar guiones bajos
    prompt = prompt.replace("_", " ")
    prompt = re.sub(r'[\[\]\(\)\{\}]', '', prompt)

    #w = w.strip(" .,:;!?-")

    # detectar tipo de prompt
    if prompt.count(",") >= 3:
        words = [w.strip() for w in prompt.split(",") if w.strip()]
    else:
        words = re.findall(r'\b[a-z0-9 ]+\b', prompt)

    # dividir palabras compuestas en texto libre
    final_words = []
    for w in words:
        # separar palabras dentro de frases
        parts = w.split()
        final_words.extend(parts if len(parts) > 1 else [w])

    # stopwords básicas
    stopwords = {
        "a","the","and","or","with","in","on","at","of","to","for",
        "is","are","this","that","it","as","an"
    }

    # limpiar
    final_words = [w for w in final_words if w not in stopwords]
    final_words = [w for w in final_words if len(w) > 2]

    return list(set(final_words))

def extract_created_at(img, input_path):
    try:
        exif = img.getexif()

        if exif:
            date_str = exif.get(36867)  # DateTimeOriginal

            if date_str:
                dt = datetime.strptime(date_str, "%Y:%m:%d %H:%M:%S")
                return dt.strftime("%Y-%m-%d %H:%M:%S")
    except:
        pass

    try:
        ts = os.path.getmtime(input_path)
        return datetime.fromtimestamp(ts).strftime("%Y-%m-%d %H:%M:%S")
    except:
        pass

    return datetime.now().strftime("%Y-%m-%d %H:%M:%S")

def get_unique_output_name(img_dir, meta_dir, base_name):
    candidate = base_name
    n = 1

    while (
        os.path.exists(os.path.join(img_dir, candidate + ".jpg")) or
        os.path.exists(os.path.join(meta_dir, candidate + ".txt"))
    ):
        candidate = f"{base_name}_{n}"
        n += 1

    return candidate

def process_image(input_path, original_name, output_base_name=None):
    img = Image.open(input_path)
    created_at = extract_created_at(img, input_path)
    width, height = img.size

    metadata = extract_metadata(img)

    import re

    model = metadata["Model"] if metadata["Model"] else "unknown"

    # limpiar caracteres problemáticos
    model = re.sub(r'[<>:"/\\|?*\[\]]', '', model)
    model = model.strip()

    base_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "public"))

    output_img_dir = os.path.join(base_dir, "data", "models", model, "images")
    output_meta_dir = os.path.join(base_dir, "data", "models", model, "metadata")

    os.makedirs(output_img_dir, exist_ok=True)
    os.makedirs(output_meta_dir, exist_ok=True)

    base_filename = output_base_name or os.path.splitext(original_name)[0]
    filename = get_unique_output_name(output_img_dir, output_meta_dir, base_filename)

    output_img_path = os.path.join(output_img_dir, filename + ".jpg")
    output_meta_path = os.path.join(output_meta_dir, filename + ".txt")

    # Convertir a JPG
    rgb_img = img.convert("RGB")
    rgb_img.save(output_img_path, "JPEG", quality=90)
    filesize = os.path.getsize(output_img_path)

    # Guardar metadatos
    with open(output_meta_path, "w", encoding="utf-8") as f:
        for key, value in metadata.items():
            f.write(f"{key}: {value}\n")

    # Salida para PHP
    web_path = f"/data/models/{model}/images/{filename}.jpg"

    keywords = extract_keywords(metadata["Prompt"])

    print("|".join([
        filename + ".jpg",
        model,
        metadata["Prompt"],
        metadata["Negative prompt"],
        metadata["Sampler"],
        metadata["Schedule type"],
        metadata["Steps"],
        metadata["CFG scale"],
        metadata["Seed"],
        web_path,
        ",".join(keywords),
        created_at,
        str(width),
        str(height),
        str(filesize)
    ]))


if __name__ == "__main__":
    input_path = sys.argv[1]
    original_name = sys.argv[2] if len(sys.argv) > 2 else os.path.basename(input_path)
    output_base_name = sys.argv[3] if len(sys.argv) > 3 else None
    process_image(input_path, original_name, output_base_name)
