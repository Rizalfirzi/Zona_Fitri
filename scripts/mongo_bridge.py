#!/usr/bin/env python3
"""
Lightweight MongoDB Atlas CLI Bridge for PHP (Zona Fitri)
Database: zona_fitri
Cluster: cluster-utama.eyzthyo.mongodb.net
Usage:
  python mongo_bridge.py get <key>
  python mongo_bridge.py set <key> <json_value_or_stdin>
  python mongo_bridge.py del <key>
  python mongo_bridge.py get_outlets <brand> [query]
  python mongo_bridge.py get_menu <brand>
"""

import sys
import os
import json
from pymongo import MongoClient

MONGODB_URI = os.getenv(
    "MONGODB_URI",
    "mongodb+srv://technologyswamp_db_user:EmmGSARebSpWS0Sw@cluster-utama.eyzthyo.mongodb.net/?retryWrites=true&w=majority&appName=Cluster-Utama"
)
DB_NAME = "zona_fitri"


def get_db():
    client = MongoClient(MONGODB_URI, serverSelectionTimeoutMS=4000)
    return client[DB_NAME]


def main():
    if len(sys.argv) < 2:
        sys.exit(1)

    cmd = sys.argv[1].lower()
    db = get_db()

    if cmd == "get":
        if len(sys.argv) < 3:
            sys.exit(1)
        key = sys.argv[2]
        col = db["settings"]
        doc = col.find_one({"_id": key})
        if doc:
            if "raw_json" in doc and doc["raw_json"]:
                sys.stdout.write(doc["raw_json"])
            elif "data" in doc:
                sys.stdout.write(json.dumps(doc["data"], ensure_ascii=False))
            else:
                sys.stdout.write(json.dumps(doc, ensure_ascii=False))
        else:
            sys.exit(2)

    elif cmd == "set":
        if len(sys.argv) < 3:
            sys.exit(1)
        key = sys.argv[2]
        col = db["settings"]
        if len(sys.argv) >= 4:
            val_str = sys.argv[3]
        else:
            val_str = sys.stdin.read()

        try:
            val_json = json.loads(val_str)
        except Exception:
            val_json = val_str

        col.update_one(
            {"_id": key},
            {"$set": {
                "_id": key,
                "data": val_json,
                "raw_json": val_str
            }},
            upsert=True
        )
        sys.stdout.write("OK")

    elif cmd == "del":
        if len(sys.argv) < 3:
            sys.exit(1)
        key = sys.argv[2]
        col = db["settings"]
        col.delete_one({"_id": key})
        sys.stdout.write("OK")

    elif cmd == "get_outlets":
        brand = sys.argv[2] if len(sys.argv) > 2 else "kopken"
        query_text = sys.argv[3].lower().strip() if len(sys.argv) > 3 else ""
        col = db["outlets"]

        filter_q = {"brand": brand}
        if query_text:
            filter_q["$or"] = [
                {"name": {"$regex": query_text, "$options": "i"}},
                {"address": {"$regex": query_text, "$options": "i"}},
                {"city": {"$regex": query_text, "$options": "i"}},
                {"code": {"$regex": query_text, "$options": "i"}},
            ]

        cursor = col.find(filter_q, {"_id": 0, "brand": 0}).limit(150)
        docs = list(cursor)
        sys.stdout.write(json.dumps(docs, ensure_ascii=False))

    elif cmd == "get_menu":
        brand = sys.argv[2] if len(sys.argv) > 2 else "kopken"
        col = db["menus_master"]
        doc = col.find_one({"brand": brand}, {"_id": 0})
        if doc:
            sys.stdout.write(json.dumps(doc, ensure_ascii=False))
        else:
            # Fallback to items list from menus collection
            col_items = db["menus"]
            cursor = col_items.find({"brand": brand}, {"_id": 0, "brand": 0})
            items = list(cursor)
            sys.stdout.write(json.dumps({"success": True, "items": items}, ensure_ascii=False))


if __name__ == "__main__":
    main()

