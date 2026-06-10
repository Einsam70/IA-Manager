import json
import os
import re
import sys
from datetime import datetime
from fractions import Fraction

from PIL import ExifTags, Image, ImageOps


EXIF_TAGS = ExifTags.TAGS
GPS_TAGS = ExifTags.GPSTAGS


def clean_slug(value):
    value = value.strip().lower()
    value = re.sub(r"[^\w\s-]", "", value, flags=re.UNICODE)
    value = re.sub(r"[\s_]+", "-", value)
    value = re.sub(r"-+", "-", value)
    return value.strip("-") or "album"


def clean_prefix(value):
    value = re.sub(r"[^a-zA-Z0-9]", "", value.upper())
    return (value[:6] or "FOTO")


def as_text(value):
    if value is None:
        return ""

    if isinstance(value, bytes):
        return value.decode("utf-8", errors="ignore").strip()

    return str(value).strip()


def rational_to_float(value):
    try:
        if isinstance(value, tuple) and len(value) == 2:
            return float(Fraction(value[0], value[1]))

        return float(value)
    except Exception:
        return None


def format_exposure(value):
    try:
        if isinstance(value, tuple) and len(value) == 2:
            if value[0] == 1:
                return f"1/{value[1]}"
            return str(float(Fraction(value[0], value[1])))

        if hasattr(value, "numerator") and hasattr(value, "denominator"):
            if value.numerator == 1:
                return f"1/{value.denominator}"
            return str(float(value))

        return as_text(value)
    except Exception:
        return as_text(value)


def parse_exif_date(value):
    value = as_text(value)

    if not value:
        return ""

    for fmt in ("%Y:%m:%d %H:%M:%S", "%Y-%m-%d %H:%M:%S"):
        try:
            return datetime.strptime(value, fmt).strftime("%Y-%m-%d %H:%M:%S")
        except ValueError:
            pass

    return value


def dms_to_decimal(value, ref):
    try:
        degrees = rational_to_float(value[0])
        minutes = rational_to_float(value[1])
        seconds = rational_to_float(value[2])

        if degrees is None or minutes is None or seconds is None:
            return None

        result = degrees + (minutes / 60) + (seconds / 3600)

        if ref in ("S", "W"):
            result *= -1

        return round(result, 8)
    except Exception:
        return None


def get_exif(img):
    try:
        raw_exif = img.getexif()
    except Exception:
        return {}

    exif = {}

    for key, value in raw_exif.items():
        name = EXIF_TAGS.get(key, key)
        exif[name] = value

    gps_info = exif.get("GPSInfo")

    if hasattr(gps_info, "items"):
        exif["GPSInfo"] = {
            GPS_TAGS.get(key, key): value
            for key, value in gps_info.items()
        }
    else:
        exif["GPSInfo"] = {}

    return exif


def extract_photo_metadata(img, input_path):
    exif = get_exif(img)
    gps = exif.get("GPSInfo", {})

    lat = dms_to_decimal(gps.get("GPSLatitude"), gps.get("GPSLatitudeRef"))
    lng = dms_to_decimal(gps.get("GPSLongitude"), gps.get("GPSLongitudeRef"))

    taken_at = parse_exif_date(
        exif.get("DateTimeOriginal")
        or exif.get("DateTimeDigitized")
        or exif.get("DateTime")
    )

    if not taken_at:
        try:
            taken_at = datetime.fromtimestamp(os.path.getmtime(input_path)).strftime("%Y-%m-%d %H:%M:%S")
        except Exception:
            taken_at = ""

    return {
        "taken_at": taken_at,
        "camera_make": as_text(exif.get("Make")),
        "camera_model": as_text(exif.get("Model")),
        "lens": as_text(exif.get("LensModel") or exif.get("LensMake")),
        "focal_length": as_text(exif.get("FocalLength")),
        "exposure_time": format_exposure(exif.get("ExposureTime")),
        "aperture": as_text(exif.get("FNumber")),
        "iso": as_text(exif.get("ISOSpeedRatings") or exif.get("PhotographicSensitivity")),
        "gps_lat": lat,
        "gps_lng": lng,
    }


def get_unique_filename(output_dir, base_name):
    candidate = base_name
    n = 1

    while os.path.exists(os.path.join(output_dir, candidate + ".jpg")):
        candidate = f"{base_name}_{n}"
        n += 1

    return candidate + ".jpg"


def process_photo(input_path, album_slug, filename_prefix, sequence, original_name):
    base_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "public"))

    album_slug = clean_slug(album_slug)
    filename_prefix = clean_prefix(filename_prefix)
    sequence = max(1, int(sequence))

    output_dir = os.path.join(base_dir, "data", "photos", album_slug, "images")
    os.makedirs(output_dir, exist_ok=True)

    img = Image.open(input_path)
    metadata = extract_photo_metadata(img, input_path)

    img = ImageOps.exif_transpose(img)
    width, height = img.size

    base_name = f"{filename_prefix}_{sequence:04d}"
    filename = get_unique_filename(output_dir, base_name)
    output_path = os.path.join(output_dir, filename)

    rgb_img = img.convert("RGB")
    rgb_img.save(output_path, "JPEG", quality=90, optimize=True)

    filesize = os.path.getsize(output_path)
    web_path = f"/data/photos/{album_slug}/images/{filename}"

    original_extension = os.path.splitext(original_name)[1].lstrip(".").lower()

    result = {
        "filename": filename,
        "original_filename": original_name,
        "original_extension": original_extension,
        "path": web_path,
        "width": width,
        "height": height,
        "filesize": filesize,
        **metadata,
    }

    print(json.dumps(result, ensure_ascii=False))


if __name__ == "__main__":
    if len(sys.argv) < 5:
        raise SystemExit("Usage: photo_processor.py <input_path> <album_slug> <filename_prefix> <sequence> [original_name]")

    input_path = sys.argv[1]
    album_slug = sys.argv[2]
    filename_prefix = sys.argv[3]
    sequence = sys.argv[4]
    original_name = sys.argv[5] if len(sys.argv) > 5 else os.path.basename(input_path)

    process_photo(input_path, album_slug, filename_prefix, sequence, original_name)
