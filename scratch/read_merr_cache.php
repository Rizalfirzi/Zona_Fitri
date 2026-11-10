<?php
$cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zonafitri_cache' . DIRECTORY_SEPARATOR . 'menu_kopken_KK_SBY_MERRNGKTDT.json';
if (file_exists($cacheFile)) {
    $data = json_decode(file_get_contents($cacheFile), true);
    foreach ($data['items'] ?? [] as $it) {
        if (stripos($it['name'], 'spaghetti') !== false || stripos($it['name'], 'bungeo') !== false || stripos($it['name'], 'chicken') !== false || stripos($it['name'], 'gyoza') !== false || stripos($it['name'], 'nugget') !== false || stripos($it['name'], 'fried') !== false || stripos($it['name'], 'nasi') !== false || stripos($it['name'], 'mie') !== false) {
            echo $it['name'] . " | " . ($it['category'] ?? '') . " | " . ($it['key'] ?? '') . "\n";
        }
    }
}

