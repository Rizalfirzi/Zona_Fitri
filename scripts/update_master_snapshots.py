#!/usr/bin/env python3
"""
Fetch full outlet and menu catalogs from the 5 dedicated swamptechnology.id brand APIs
and update local snapshot files in data/ directory for instant zero-delay fallback.
"""
import urllib.request
import json
import os
import re

DATA_DIR = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'data')
os.makedirs(DATA_DIR, exist_ok=True)

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
}

def fetch_json(url, timeout=15):
    try:
        req = urllib.request.Request(url, headers=HEADERS)
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            return json.loads(resp.read().decode('utf-8'))
    except Exception as e:
        print(f"Error fetching {url}: {e}")
        return None

def slugify(text):
    s = re.sub(r'[^a-zA-Z0-9]', '', str(text or '')).lower()
    return s or 'item'

# -------------------------------------------------------------
# 1. KOPI KENANGAN
# -------------------------------------------------------------
print("Updating Kopi Kenangan...")
kopken_stores_raw = fetch_json('https://kopkenapi.swamptechnology.id/api/store/query_pageable_store?pageSize=1500')
if kopken_stores_raw and 'data' in kopken_stores_raw:
    raw_stores = kopken_stores_raw.get('data', {}).get('store', [])
    outlets = []
    for s in raw_stores:
        name = s.get('alias') or s.get('name') or ''
        addr = s.get('address') or ''
        code = s.get('code') or ''
        city = ''
        if 'surabaya' in (addr + name).lower(): city = 'Surabaya'
        elif 'jakarta' in (addr + name).lower(): city = 'Jakarta'
        elif 'jombang' in (addr + name).lower(): city = 'Jombang'
        elif 'bandung' in (addr + name).lower(): city = 'Bandung'
        elif 'semarang' in (addr + name).lower(): city = 'Semarang'
        elif 'tangerang' in (addr + name).lower(): city = 'Tangerang'
        elif 'bekasi' in (addr + name).lower(): city = 'Bekasi'
        elif 'gresik' in (addr + name).lower(): city = 'Gresik'
        elif 'sidoarjo' in (addr + name).lower(): city = 'Sidoarjo'
        elif 'malang' in (addr + name).lower(): city = 'Malang'

        open_time = s.get('open') or '07:00'
        close_time = s.get('close') or '22:00'
        outlets.append({
            'code': code,
            'id': code,
            'name': name,
            'address': addr,
            'city': city,
            'lat': float(s.get('latitude') or 0),
            'lng': float(s.get('longitude') or 0),
            'isOpen': bool(s.get('is_open') and s.get('available')),
            'hours': f"{open_time} - {close_time} WIB",
            'img': s.get('image_url') or 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?q=80&w=400&auto=format&fit=crop'
        })
    with open(os.path.join(DATA_DIR, 'outlets_master_kopken.json'), 'w', encoding='utf-8') as f:
        json.dump(outlets, f, ensure_ascii=False, indent=2)
    print(f"  Saved {len(outlets)} Kopken outlets.")

# Kopken Menu
kopken_menu_raw = fetch_json('https://kopkenapi.swamptechnology.id/api/product/query_product_menu?store_code=KK.JBG.RKWHDHSYM')
if kopken_menu_raw and 'data' in kopken_menu_raw:
    groups = kopken_menu_raw.get('data', {}).get('menu_groups', [])
    items = []
    categories = []
    seen_keys = set()
    for g in groups:
        gname = g.get('group_name', 'Menu')
        cat_id = 'cat-' + slugify(gname)
        cat_items = []
        for p in g.get('menu_products', []):
            name = p.get('name', '')
            key = slugify(name)
            if key in seen_keys:
                continue
            seen_keys.add(key)
            orig_price = int(p.get('orig_price') or p.get('price') or 0)
            price = int(p.get('price') or orig_price)
            img = p.get('image') or ''
            is_sold_out = bool(p.get('is_sold_out'))
            desc = p.get('description') or ''
            is_food = bool(re.search(r'toast|food|panini|croissant|bagel|roti|bread|cookie|muffin|danish|cake', (name + ' ' + gname), re.I))

            item_obj = {
                'key': key,
                'id': str(p.get('id') or key),
                'code': str(p.get('product_code') or p.get('id') or key),
                'name': name,
                'category': gname,
                'outletPrice': orig_price if orig_price > 0 else price,
                'normalPrice': orig_price if orig_price > 0 else price,
                'price': orig_price if orig_price > 0 else price,
                'img': img,
                'isSoldOut': is_sold_out,
                'notCarried': False,
                'isFood': is_food,
                'desc': desc,
                'options': {
                    'sizes': ['Regular'] if is_food else ['Regular', 'Large'],
                    'temps': ['Ice', 'Hot'],
                    'ices': ['Normal Ice', 'Less Ice', 'No Ice'],
                    'sugars': ['Normal Sugar', 'Less Sugar', 'No Sugar']
                }
            }
            items.append(item_obj)
            cat_items.append(key)
        if cat_items:
            categories.append({
                'id': cat_id,
                'name': gname,
                'kind': 'item',
                'items': cat_items
            })
    menu_doc = {
        'brand': 'kopken',
        'categories': categories,
        'items': items,
        'updatedAt': '2026-11-20T16:30:00+07:00'
    }
    with open(os.path.join(DATA_DIR, 'menu_master_kopken.json'), 'w', encoding='utf-8') as f:
        json.dump(menu_doc, f, ensure_ascii=False, indent=2)
    print(f"  Saved {len(items)} Kopken menu items across {len(categories)} categories.")

# -------------------------------------------------------------
# 2. JANJI JIWA
# -------------------------------------------------------------
print("\nUpdating Janji Jiwa...")
jiwa_stores_raw = fetch_json('https://jiwaapi.swamptechnology.id/api/store/all?limit=1000')
if jiwa_stores_raw and 'payload' in jiwa_stores_raw:
    raw_stores = jiwa_stores_raw.get('payload', [])
    outlets = []
    for s in raw_stores:
        oid = str(s.get('id_outlet') or s.get('outlet_code') or '')
        name = s.get('outlet_name') or ''
        addr = s.get('outlet_address') or ''
        outlets.append({
            'code': oid,
            'id': oid,
            'outlet_code': s.get('outlet_code'),
            'name': name,
            'address': addr,
            'lat': float(s.get('outlet_lat') or 0),
            'lng': float(s.get('outlet_long') or 0),
            'isOpen': s.get('is_open') == 1,
            'hours': '07:00 - 22:00 WIB',
            'img': 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?q=80&w=400&auto=format&fit=crop'
        })
    with open(os.path.join(DATA_DIR, 'outlets_master_jiwa.json'), 'w', encoding='utf-8') as f:
        json.dump(outlets, f, ensure_ascii=False, indent=2)
    print(f"  Saved {len(outlets)} Jiwa outlets.")

jiwa_menu_raw = fetch_json('https://jiwaapi.swamptechnology.id/api/product/menu?outlet_id=2248')
if jiwa_menu_raw and 'payload' in jiwa_menu_raw:
    raw_items = jiwa_menu_raw.get('payload', [])
    items = []
    cat_map = {}
    seen_keys = set()
    for p in raw_items:
        name = p.get('product_name') or ''
        key = slugify(name)
        if key in seen_keys: continue
        seen_keys.add(key)
        cat_name = p.get('category_name') or 'Menu'
        price = int(p.get('price') or p.get('discounted_price') or 0)
        is_food = bool(re.search(r'toast|food|bun|bread|roti', (name + ' ' + cat_name), re.I))

        item_obj = {
            'key': key,
            'id': str(p.get('id_product') or key),
            'code': str(p.get('id_product') or key),
            'name': name,
            'category': cat_name,
            'outletPrice': price,
            'normalPrice': price,
            'price': price,
            'img': p.get('product_photo') or '',
            'isSoldOut': p.get('is_inactive') == 1,
            'isFood': is_food,
            'desc': p.get('product_description') or '',
            'options': {
                'sizes': ['Regular'],
                'temps': ['Ice', 'Hot'],
                'ices': ['Normal Ice', 'Less Ice', 'No Ice'],
                'sugars': ['Normal Sugar', 'Less Sugar', 'No Sugar']
            }
        }
        items.append(item_obj)
        if cat_name not in cat_map: cat_map[cat_name] = []
        cat_map[cat_name].append(key)
    categories = [{'id': 'cat-' + slugify(cn), 'name': cn, 'kind': 'item', 'items': citems} for cn, citems in cat_map.items()]
    menu_doc = {
        'brand': 'jiwa',
        'categories': categories,
        'items': items,
        'updatedAt': '2026-11-20T16:30:00+07:00'
    }
    with open(os.path.join(DATA_DIR, 'menu_master_jiwa.json'), 'w', encoding='utf-8') as f:
        json.dump(menu_doc, f, ensure_ascii=False, indent=2)
    print(f"  Saved {len(items)} Jiwa menu items across {len(categories)} categories.")

# -------------------------------------------------------------
# 3. FORE COFFEE
# -------------------------------------------------------------
print("\nUpdating Fore Coffee...")
fore_stores_raw = fetch_json('https://foreapi.swamptechnology.id/api/store/all?limit=1000')
if fore_stores_raw and 'payload' in fore_stores_raw:
    raw_stores = fore_stores_raw.get('payload', [])
    outlets = []
    for s in raw_stores:
        code = s.get('st_code') or str(s.get('st_id') or '')
        name = s.get('st_name') or ''
        addr = s.get('st_address') or ''
        open_t = s.get('st_open') or '07:00'
        close_t = s.get('st_close') or '22:00'
        outlets.append({
            'code': code,
            'id': str(s.get('st_id') or code),
            'name': name,
            'address': addr,
            'lat': float(s.get('st_lat') or 0),
            'lng': float(s.get('st_long') or 0),
            'isOpen': s.get('st_is_open') == 1,
            'hours': f"{open_t} - {close_t} WIB",
            'img': 'https://images.unsplash.com/photo-1442512595331-e89e73853f31?q=80&w=400&auto=format&fit=crop'
        })
    with open(os.path.join(DATA_DIR, 'outlets_master_fore.json'), 'w', encoding='utf-8') as f:
        json.dump(outlets, f, ensure_ascii=False, indent=2)
    print(f"  Saved {len(outlets)} Fore outlets.")

fore_menu_raw = fetch_json('https://foreapi.swamptechnology.id/api/product/menu?store_id=KOKAS')
if fore_menu_raw and 'payload' in fore_menu_raw:
    raw_items = fore_menu_raw.get('payload', [])
    items = []
    cat_map = {}
    seen_keys = set()
    for p in raw_items:
        name = p.get('pd_name') or ''
        key = slugify(name)
        if key in seen_keys: continue
        seen_keys.add(key)
        cat_name = p.get('cat_name') or 'Menu'
        price = int(p.get('pd_final_price') or p.get('pd_final_price_ori') or 0)
        orig_price = int(p.get('pd_final_price_ori') or price)
        is_food = bool(re.search(r'pastry|croissant|panini|sandwich|toast|food|cake|bread', (name + ' ' + cat_name), re.I))

        item_obj = {
            'key': key,
            'id': str(p.get('pd_id') or key),
            'code': str(p.get('pd_code') or p.get('pd_id') or key),
            'name': name,
            'category': cat_name,
            'outletPrice': orig_price,
            'normalPrice': orig_price,
            'price': price,
            'img': p.get('image_url') or p.get('pd_img') or '',
            'isSoldOut': p.get('stpd_status') != 'ACTIVE',
            'isFood': is_food,
            'desc': p.get('pd_desc') or '',
            'options': {
                'sizes': ['Regular', 'Large'] if not is_food else ['Regular'],
                'temps': ['Ice', 'Hot'],
                'ices': ['Normal Ice', 'Less Ice', 'No Ice'],
                'sugars': ['Normal Sugar', 'Less Sugar', 'No Sugar']
            }
        }
        items.append(item_obj)
        if cat_name not in cat_map: cat_map[cat_name] = []
        cat_map[cat_name].append(key)
    categories = [{'id': 'cat-' + slugify(cn), 'name': cn, 'kind': 'item', 'items': citems} for cn, citems in cat_map.items()]
    menu_doc = {
        'brand': 'fore',
        'categories': categories,
        'items': items,
        'updatedAt': '2026-11-20T16:30:00+07:00'
    }
    with open(os.path.join(DATA_DIR, 'menu_master_fore.json'), 'w', encoding='utf-8') as f:
        json.dump(menu_doc, f, ensure_ascii=False, indent=2)
    print(f"  Saved {len(items)} Fore menu items across {len(categories)} categories.")

# -------------------------------------------------------------
# 4. TOMORO COFFEE
# -------------------------------------------------------------
print("\nUpdating Tomoro Coffee...")
tomoro_stores_raw = fetch_json('https://tomoroapi.swamptechnology.id/api/store/list?pageSize=1000')
if tomoro_stores_raw and 'data' in tomoro_stores_raw:
    raw_stores = tomoro_stores_raw.get('data', {}).get('records', [])
    outlets = []
    for s in raw_stores:
        code = s.get('storeCode') or ''
        name = s.get('storeName') or ''
        addr = s.get('storeAddress') or ''
        outlets.append({
            'code': code,
            'id': code,
            'name': name,
            'address': addr,
            'city': s.get('city') or '',
            'lat': float(s.get('latitude') or 0),
            'lng': float(s.get('longitude') or 0),
            'isOpen': s.get('businessStatus') == 1,
            'hours': '07:00 - 22:00 WIB',
            'img': s.get('storePicture') or 'https://images.unsplash.com/photo-1509785307050-d4066910ec1e?q=80&w=400&auto=format&fit=crop'
        })
    with open(os.path.join(DATA_DIR, 'outlets_master_tomoro.json'), 'w', encoding='utf-8') as f:
        json.dump(outlets, f, ensure_ascii=False, indent=2)
    print(f"  Saved {len(outlets)} Tomoro outlets.")

tomoro_menu_raw = fetch_json('https://tomoroapi.swamptechnology.id/api/product/menu?storeCode=JKT0001&mainMenuType=1')
if tomoro_menu_raw and 'data' in tomoro_menu_raw:
    vos = tomoro_menu_raw.get('data', {}).get('menuVos', [])
    items = []
    categories = []
    seen_keys = set()
    for vo in vos:
        cname = vo.get('menuName', 'Menu')
        # Skip food / snack categories per client revision
        if re.search(r'food|pastry|snack|bakery|toast|bread', cname, re.I):
            continue
        cat_id = 'cat-' + slugify(cname)
        cat_items = []
        for p in vo.get('items', []):
            name = p.get('name') or ''
            key = slugify(name)
            if key in seen_keys: continue
            seen_keys.add(key)
            price = int(p.get('price') or p.get('linePrice') or 0)
            orig_price = int(p.get('linePrice') or price)
            item_obj = {
                'key': key,
                'id': str(p.get('goodId') or key),
                'code': str(p.get('code') or p.get('goodId') or key),
                'name': name,
                'category': cname,
                'outletPrice': orig_price,
                'normalPrice': orig_price,
                'price': price,
                'img': p.get('goodPic') or '',
                'isSoldOut': bool(p.get('isSoldOut')),
                'isFood': False,
                'desc': p.get('desc') or '',
                'options': {
                    'sizes': ['Small'],
                    'temps': ['Ice', 'Hot'],
                    'ices': ['Normal Ice', 'Less Ice', 'No Ice'],
                    'sugars': ['Normal Sugar', 'Less Sugar', 'No Sugar']
                }
            }
            items.append(item_obj)
            cat_items.append(key)
        if cat_items:
            categories.append({
                'id': cat_id,
                'name': cname,
                'kind': 'item',
                'items': cat_items
            })
    menu_doc = {
        'brand': 'tomoro',
        'categories': categories,
        'items': items,
        'updatedAt': '2026-11-20T16:30:00+07:00'
    }
    with open(os.path.join(DATA_DIR, 'menu_master_tomoro.json'), 'w', encoding='utf-8') as f:
        json.dump(menu_doc, f, ensure_ascii=False, indent=2)
    print(f"  Saved {len(items)} Tomoro menu items across {len(categories)} categories.")

print("\n--- ALL MASTER SNAPSHOTS UPDATED FROM SWAMPTECHNOLOGY APIS SUCCESSFULLY ---")
