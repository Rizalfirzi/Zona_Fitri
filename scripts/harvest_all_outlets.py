#!/usr/bin/env python3
"""
Zona Fitri - High-Speed Nationwide Multi-Brand Outlet Harvester
Brands: Kopi Kenangan, Tomoro Coffee, Fore Coffee, Janji Jiwa
Concurrent Multithreaded Engine with ThreadPoolExecutor
"""

import os
import sys
import time
import json
import urllib.request
import urllib.parse
import http.cookiejar
from datetime import datetime, timezone
from concurrent.futures import ThreadPoolExecutor, as_completed
from pymongo import MongoClient, UpdateOne

MONGODB_URI = os.getenv(
    "MONGODB_URI",
    "mongodb+srv://technologyswamp_db_user:EmmGSARebSpWS0Sw@cluster-utama.eyzthyo.mongodb.net/?retryWrites=true&w=majority&appName=Cluster-Utama"
)
DB_NAME = "zona_fitri"

USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"

SOURCES = [
    "https://twinsjasdor.my.id",
    "https://tixandcoffee.developerngantuk.my.id",
    "https://ridstore.developerngantuk.my.id",
    "https://tyaastore.my.id"
]

CITIES_AND_KEYWORDS = [
    "karawang", "cikampek", "telukjambe", "klari", "galuh mas", "resinda",
    "jakarta", "jaksel", "jakpus", "jaktim", "jakbar", "jakut",
    "bogor", "depok", "tangerang", "bekasi", "cikarang", "bintaro", "serpong", "bsd", 
    "karawaci", "cibubur", "alam sutera", "gading serpong", "kelapa gading", "senayan",
    "thamrin", "sudirman", "kuningan", "kemang", "pik", "pluit",
    "bandung", "cimahi", "cirebon", "sukabumi", "tasikmalaya", "garut", "purwakarta", 
    "subang", "indramayu", "majalengka", "kuningan", "cianjur", "sumedang",
    "semarang", "solo", "surakarta", "yogyakarta", "jogja", "magelang", "salatiga", 
    "purwokerto", "tegal", "pekalongan", "kudus", "pati", "klaten", "cilacap",
    "surabaya", "malang", "sidoarjo", "gresik", "pasuruan", "mojokerto", 
    "jember", "banyuwangi", "kediri", "madiun", "blitar", "probolinggo", "batu", "tuban",
    "bali", "denpasar", "kuta", "seminyak", "ubud", "badung", "mataram", "lombok", "kupang",
    "medan", "palembang", "pekanbaru", "batam", "padang", "lampung", "jambi", "bengkulu", "aceh", 
    "balikpapan", "samarinda", "banjarmasin", "pontianak", "palangkaraya", "tarakan",
    "makassar", "manado", "palu", "kendari", "gorontalo", "ambon", "jayapura",
    "mall", "plaza", "square", "stasiun", "bandara", "rest area", "spbu",
    "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", 
    "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"
]

DEFAULT_PHOTOS = {
    "kopken": "https://cdn.kopikenangan.com/public/cf8b6835-e066-4a73-7f08-4803716d2d4c",
    "fore": "https://static.fore.coffee/store_image/AEONTanjungBarat-181121.jpg",
    "tomoro": "https://images.unsplash.com/photo-1554118811-1e0d58224f24?q=80&w=400&auto=format&fit=crop",
    "jiwa": "https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?q=80&w=400&auto=format&fit=crop"
}

def clean_outlet(o, brand):
    code = str(o.get("code") or o.get("id") or "").strip()
    name = str(o.get("name") or "").strip()
    if not code or not name:
        return None

    img = o.get("img") or o.get("image") or o.get("photo") or ""
    if img and img.startswith("/"):
        img = "https://twinsjasdor.my.id" + img
    if not img:
        img = DEFAULT_PHOTOS.get(brand, "")

    lat = o.get("lat")
    lng = o.get("lng")
    try:
        lat = float(lat) if lat is not None else None
    except (ValueError, TypeError):
        lat = None
    try:
        lng = float(lng) if lng is not None else None
    except (ValueError, TypeError):
        lng = None

    return {
        "code": code,
        "name": name,
        "address": o.get("address") or "",
        "city": o.get("city") or "",
        "lat": lat,
        "lng": lng,
        "isOpen": bool(o.get("isOpen", True)),
        "canOrder": bool(o.get("canOrder", True)),
        "statusText": o.get("statusText") or ("Buka" if o.get("isOpen", True) else "Tutup"),
        "hours": o.get("hours") or "08:00 - 22:00",
        "img": img,
        "category": o.get("category") or o.get("type") or "Outlet",
        "phone": o.get("phone") or "",
        "brand": brand,
        "updated_at": datetime.now(timezone.utc)
    }

def fetch_task(base, brand, kw, opener, headers):
    url = f"{base}/api/{brand}/outlets?q={urllib.parse.quote(kw)}"
    try:
        req = urllib.request.Request(url, headers=headers)
        with opener.open(req, timeout=4) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            items = data.get("outlets", []) if isinstance(data, dict) else (data if isinstance(data, list) else [])
            return brand, items
    except Exception:
        return brand, []

def main():
    print("=" * 70, flush=True)
    print("  ZONA FITRI - HIGH-SPEED NATIONWIDE MULTI-BRAND OUTLET HARVESTER", flush=True)
    print("=" * 70, flush=True)
    start_time = time.time()

    brand_outlets = {
        "kopken": {},
        "tomoro": {},
        "fore": {},
        "jiwa": {}
    }

    # 1. Load Kyuti 1,148 Kopi Kenangan outlets directly
    kyuti_file = "E:/swam/kedai kyuti/kopken/data/outlets_master.json"
    if os.path.exists(kyuti_file):
        try:
            with open(kyuti_file, "r", encoding="utf-8") as f:
                data = json.load(f)
                count = 0
                for o in data:
                    c = clean_outlet(o, "kopken")
                    if c:
                        brand_outlets["kopken"][c["code"]] = c
                        count += 1
                print(f"[+] Loaded {count} Kopi Kenangan outlets from Kyuti master database!", flush=True)
        except Exception as e:
            print(f"[!] Warning reading Kyuti master file: {e}", flush=True)

    # 2. Load existing local master Tomoro 645 outlets
    tomoro_file = "data/outlets_master_tomoro.json"
    if os.path.exists(tomoro_file):
        try:
            with open(tomoro_file, "r", encoding="utf-8") as f:
                data = json.load(f)
                count = 0
                for o in data:
                    c = clean_outlet(o, "tomoro")
                    if c:
                        brand_outlets["tomoro"][c["code"]] = c
                        count += 1
                print(f"[+] Loaded {count} Tomoro Coffee outlets from local master catalog!", flush=True)
        except Exception as e:
            print(f"[!] Warning reading local Tomoro catalog: {e}", flush=True)

    # 3. Setup Openers for Upstream Sources
    openers = {}
    for base in SOURCES:
        cj = http.cookiejar.CookieJar()
        op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
        try:
            req_home = urllib.request.Request(f"{base}/", headers={"User-Agent": USER_AGENT})
            op.open(req_home, timeout=5)
            openers[base] = op
            print(f"[+] Upstream handshake OK: {base}", flush=True)
        except Exception as e:
            print(f"[!] Upstream handshake failed for {base}: {e}", flush=True)

    headers_template = {
        "User-Agent": USER_AGENT,
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest"
    }

    # 4. Schedule concurrent tasks
    tasks = []
    print("\n[*] Dispatching concurrent tasks across all brands and regions...", flush=True)
    with ThreadPoolExecutor(max_workers=20) as executor:
        for base, op in openers.items():
            h = dict(headers_template)
            h["Referer"] = f"{base}/"
            for brand in ["kopken", "fore", "jiwa", "tomoro"]:
                for kw in CITIES_AND_KEYWORDS:
                    tasks.append(executor.submit(fetch_task, base, brand, kw, op, h))

        completed_tasks = 0
        total_tasks = len(tasks)
        for future in as_completed(tasks):
            completed_tasks += 1
            brand, items = future.result()
            for raw in items:
                cleaned = clean_outlet(raw, brand)
                if cleaned:
                    code = cleaned["code"]
                    if code not in brand_outlets[brand]:
                        brand_outlets[brand][code] = cleaned
                    else:
                        if cleaned.get("img") and not brand_outlets[brand][code].get("img"):
                            brand_outlets[brand][code]["img"] = cleaned["img"]
                        if cleaned.get("phone") and not brand_outlets[brand][code].get("phone"):
                            brand_outlets[brand][code]["phone"] = cleaned["phone"]

            if completed_tasks % 200 == 0 or completed_tasks == total_tasks:
                print(f"    Progress: {completed_tasks}/{total_tasks} tasks ({int(completed_tasks/total_tasks*100)}%) | Kopken: {len(brand_outlets['kopken'])}, Tomoro: {len(brand_outlets['tomoro'])}, Fore: {len(brand_outlets['fore'])}, Jiwa: {len(brand_outlets['jiwa'])}", flush=True)

    # 5. Connect to MongoDB Atlas and bulk write
    print("\n[*] Connecting to MongoDB Atlas ('zona_fitri')...", flush=True)
    try:
        client = MongoClient(MONGODB_URI, serverSelectionTimeoutMS=8000)
        db = client[DB_NAME]
        col = db["outlets"]
        print("[+] MongoDB Atlas connected!", flush=True)
    except Exception as e:
        print(f"[!] MongoDB Atlas connection error: {e}", flush=True)
        col = None

    os.makedirs("data", exist_ok=True)
    total_all_brands = 0

    print("\n[*] Writing master snapshot JSON files to data/ and upserting into MongoDB Atlas...", flush=True)
    for brand, outlets_dict in brand_outlets.items():
        outlets_list = list(outlets_dict.values())
        count = len(outlets_list)
        total_all_brands += count

        filepath = f"data/outlets_master_{brand}.json"
        with open(filepath, "w", encoding="utf-8") as f:
            serializable = []
            for o in outlets_list:
                item = dict(o)
                if isinstance(item.get("updated_at"), datetime):
                    item["updated_at"] = item["updated_at"].isoformat()
                serializable.append(item)
            json.dump(serializable, f, ensure_ascii=False, indent=2)
        print(f"[+] Saved {count} outlets to {filepath}", flush=True)

        if col is not None and outlets_list:
            ops = []
            for o in outlets_list:
                doc = dict(o)
                code = doc["code"]
                ops.append(UpdateOne(
                    {"brand": brand, "code": code},
                    {"$set": doc},
                    upsert=True
                ))
            if ops:
                res = col.bulk_write(ops, ordered=False)
                print(f"    MongoDB Atlas: {res.upserted_count} inserted, {res.modified_count} updated for {brand}", flush=True)

    elapsed = round(time.time() - start_time, 1)
    print("\n" + "=" * 70, flush=True)
    print(f"  HARVEST COMPLETE in {elapsed}s!", flush=True)
    print(f"  TOTAL OUTLETS HARVESTED ACROSS INDONESIA: {total_all_brands}", flush=True)
    for brand, outlets_dict in brand_outlets.items():
        print(f"    - {brand.upper()}: {len(outlets_dict)} gerai", flush=True)
    print("=" * 70, flush=True)

if __name__ == "__main__":
    main()
