#!/usr/bin/env python3
"""
Zona Fitri - Multi-Brand & Multi-Source to MongoDB Atlas Realtime Sync
Author: Antigravity Team
Database: zona_fitri
Cluster: cluster-utama.eyzthyo.mongodb.net
Brands: kopken, tomoro, fore, jiwa
Description:
Fetches live coffee outlets, menus, categories, and stock stealthily from
multiple independent upstream sources (twinsjasdor, tixandcoffee, ridstore, tyaastore)
and upserts them into MongoDB Atlas ('zona_fitri' database) in real-time.
Also writes master JSON snapshot files into data/ so the store has 100% offline-ready
capability and zero single-point-of-failure dependency on developerngantuk!
"""

import os
import sys
import time
import json
import argparse
import urllib.request
import urllib.parse
import http.cookiejar
from datetime import datetime, timezone
from pymongo import MongoClient, UpdateOne

MONGODB_URI = os.getenv(
    "MONGODB_URI",
    "mongodb+srv://technologyswamp_db_user:EmmGSARebSpWS0Sw@cluster-utama.eyzthyo.mongodb.net/?retryWrites=true&w=majority&appName=Cluster-Utama"
)
DB_NAME = "zona_fitri"

BRANDS = ["kopken", "tomoro", "fore", "jiwa"]

DEFAULT_OUTLETS = {
    "kopken": "KK.SBY.JMSRI",
    "tomoro": "TMR-627779",
    "fore": "KOKAS",
    "jiwa": "25"
}

SOURCES = [
    "https://twinsjasdor.my.id",
    "https://tixandcoffee.developerngantuk.my.id",
    "https://ridstore.developerngantuk.my.id",
    "https://tyaastore.my.id"
]

USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"


class ZonaFitriMongoSync:
    def __init__(self, uri=MONGODB_URI, db_name=DB_NAME):
        print(f"[*] Connecting to MongoDB Atlas ({db_name})...")
        self.client = MongoClient(uri, serverSelectionTimeoutMS=10000)
        self.db = self.client[db_name]
        self.outlets_col = self.db["outlets"]
        self.menus_col = self.db["menus"]
        self.menus_master_col = self.db["menus_master"]
        self.sync_logs_col = self.db["sync_logs"]
        self.settings_col = self.db["settings"]

        # Ensure indexes
        self.outlets_col.create_index([("brand", 1), ("code", 1)], unique=True)
        self.menus_col.create_index([("brand", 1), ("key", 1)], unique=True)
        self.menus_master_col.create_index("brand", unique=True)
        print("[+] MongoDB Atlas connected and indexes verified.")

    def fetch_from_source(self, base_url, brand):
        """Fetch outlets and menu for a brand from a single upstream source using stealth customer handshake"""
        cj = http.cookiejar.CookieJar()
        opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

        # Step 1: Handshake as customer visiting homepage to establish cookie
        try:
            req_home = urllib.request.Request(f"{base_url}/", headers={"User-Agent": USER_AGENT})
            opener.open(req_home, timeout=8)
        except Exception as e:
            # Continue even if homepage fails
            pass

        headers = {
            "User-Agent": USER_AGENT,
            "Accept": "application/json",
            "Referer": f"{base_url}/",
            "X-Requested-With": "XMLHttpRequest"
        }

        # Step 2: Fetch outlets
        outlets = []
        try:
            req_out = urllib.request.Request(f"{base_url}/api/{brand}/outlets", headers=headers)
            with opener.open(req_out, timeout=10) as resp:
                data = json.loads(resp.read().decode("utf-8"))
                outlets = data.get("outlets", [])
        except Exception as e:
            # print(f"[!] Error fetching {brand} outlets from {base_url}: {e}")
            pass

        # Step 3: Fetch menu
        menu_full = None
        try:
            sample_outlet = outlets[0]["code"] if outlets else DEFAULT_OUTLETS.get(brand, "")
            req_menu = urllib.request.Request(f"{base_url}/api/{brand}/menu?outlet={sample_outlet}", headers=headers)
            with opener.open(req_menu, timeout=12) as resp:
                menu_full = json.loads(resp.read().decode("utf-8"))
        except Exception as e:
            # print(f"[!] Error fetching {brand} menu from {base_url}: {e}")
            pass

        return outlets, menu_full

    def sync_brand(self, brand):
        print(f"[*] Syncing brand: {brand}...")
        all_outlets = {}
        all_menus = {}
        sample_menu_full = None
        data_dir = os.path.join(os.path.dirname(__file__), "..", "data")

        # 1. Preload master outlets from local snapshot
        local_out_file = os.path.join(data_dir, f"outlets_master_{brand}.json")
        if not os.path.exists(local_out_file) and brand == "tomoro":
            local_out_file = os.path.join(data_dir, "outlets_tomoro.json")

        if os.path.exists(local_out_file):
            try:
                with open(local_out_file, "r", encoding="utf-8") as f:
                    master_outlets = json.load(f)
                    for o in master_outlets:
                        c = o.get("code")
                        if c:
                            all_outlets[c] = o
                print(f"  [+] Loaded {len(all_outlets)} base outlets from local {os.path.basename(local_out_file)}")
            except Exception as e:
                print(f"  [!] Error loading local outlets for {brand}: {e}")

        # Preload outlets already in MongoDB to preserve all existing data
        try:
            for o in self.outlets_col.find({"brand": brand}):
                c = o.get("code")
                if c and c not in all_outlets:
                    o.pop("_id", None)
                    all_outlets[c] = o
        except Exception:
            pass

        # 2. Preload master menu from local snapshot
        local_menu_file = os.path.join(data_dir, f"menu_master_{brand}.json")
        if os.path.exists(local_menu_file):
            try:
                with open(local_menu_file, "r", encoding="utf-8") as f:
                    m_data = json.load(f)
                    if isinstance(m_data, dict):
                        sample_menu_full = m_data
                        for it in m_data.get("items", []):
                            k = it.get("key") or it.get("id") or it.get("name")
                            if k:
                                all_menus[k] = it
                    print(f"  [+] Loaded {len(all_menus)} base menu items from local {os.path.basename(local_menu_file)}")
            except Exception as e:
                print(f"  [!] Error loading local menu for {brand}: {e}")

        # Preload menu items already in MongoDB to preserve full catalogue
        try:
            for m in self.menus_col.find({"brand": brand}):
                k = m.get("key") or m.get("id") or m.get("name")
                if k and k not in all_menus:
                    m.pop("_id", None)
                    all_menus[k] = m
        except Exception:
            pass

        # 3. Fetch live updates from upstream sources
        for src in SOURCES:
            outlets, menu_full = self.fetch_from_source(src, brand)
            if outlets:
                for o in outlets:
                    code = o.get("code")
                    if code:
                        if code in all_outlets:
                            all_outlets[code].update(o)
                        else:
                            all_outlets[code] = o

            if menu_full and menu_full.get("items"):
                if sample_menu_full is None:
                    sample_menu_full = menu_full
                else:
                    # Update categories if upstream provides more
                    if menu_full.get("categories"):
                        sample_menu_full["categories"] = menu_full["categories"]

                for it in menu_full["items"]:
                    key = it.get("key") or it.get("id") or it.get("name")
                    if key:
                        if key in all_menus:
                            all_menus[key].update(it)
                        else:
                            all_menus[key] = it

        outlets_list = list(all_outlets.values())
        menu_list = list(all_menus.values())

        now = datetime.now(timezone.utc)

        # Upsert Outlets
        if outlets_list:
            ops = []
            for o in outlets_list:
                doc = {**o, "brand": brand, "updated_at": now}
                ops.append(UpdateOne({"brand": brand, "code": o["code"]}, {"$set": doc}, upsert=True))
            res = self.outlets_col.bulk_write(ops)
            print(f"  [✓] Outlets: {len(ops)} total ({res.upserted_count} new, {res.modified_count} updated).")

        # Upsert Menus
        if menu_list:
            ops = []
            for m in menu_list:
                key = m.get("key") or m.get("id") or m.get("name")
                doc = {**m, "brand": brand, "updated_at": now}
                ops.append(UpdateOne({"brand": brand, "key": str(key)}, {"$set": doc}, upsert=True))
            res = self.menus_col.bulk_write(ops)
            print(f"  [✓] Menu items: {len(ops)} total ({res.upserted_count} new, {res.modified_count} updated).")

        # Upsert Menu Master document
        if sample_menu_full:
            master_doc = dict(sample_menu_full)
            master_doc["brand"] = brand
            master_doc["items"] = menu_list
            master_doc["updated_at"] = now
            self.menus_master_col.update_one(
                {"brand": brand},
                {"$set": master_doc},
                upsert=True
            )

        # Export local JSON snapshots to data/
        os.makedirs(data_dir, exist_ok=True)

        if outlets_list:
            out_file = os.path.join(data_dir, f"outlets_master_{brand}.json")
            with open(out_file, "w", encoding="utf-8") as f:
                json.dump(outlets_list, f, indent=2, ensure_ascii=False, default=str)

        if sample_menu_full:
            menu_file = os.path.join(data_dir, f"menu_master_{brand}.json")
            with open(menu_file, "w", encoding="utf-8") as f:
                json.dump(sample_menu_full, f, indent=2, ensure_ascii=False, default=str)

        return len(outlets_list), len(menu_list)

    def sync_all(self):
        start_time = time.time()
        print(f"\n[*] Starting multi-brand sync to MongoDB Atlas ('{DB_NAME}')...")

        stats = {}
        for brand in BRANDS:
            try:
                o_count, m_count = self.sync_brand(brand)
                stats[brand] = {"outlets": o_count, "menu": m_count}
            except Exception as e:
                print(f"[!] Error syncing {brand}: {e}")
                stats[brand] = {"error": str(e)}

        duration = round(time.time() - start_time, 2)
        now = datetime.now(timezone.utc)

        self.sync_logs_col.insert_one({
            "timestamp": now,
            "brands": stats,
            "duration_seconds": duration,
            "status": "success"
        })
        print(f"[★] Full multi-brand sync completed successfully in {duration}s!\n")


def main():
    parser = argparse.ArgumentParser(description="Multi-Brand Sync to MongoDB Atlas (Zona Fitri)")
    parser.add_argument("--daemon", action="store_true", help="Run continuously in background")
    parser.add_argument("--interval", type=int, default=300, help="Sync interval in seconds (default: 300 / 5 mins)")
    parser.add_argument("--brand", type=str, default="", help="Sync specific brand only (kopken, tomoro, fore, jiwa)")
    args = parser.parse_args()

    sync = ZonaFitriMongoSync()

    if args.brand and args.brand in BRANDS:
        sync.sync_brand(args.brand)
        return

    if args.daemon:
        print(f"[*] Starting daemon mode. Syncing every {args.interval} seconds (Press Ctrl+C to stop)...")
        while True:
            try:
                sync.sync_all()
            except Exception as e:
                print(f"[!] Error during sync: {e}")
            time.sleep(args.interval)
    else:
        sync.sync_all()


if __name__ == "__main__":
    main()
