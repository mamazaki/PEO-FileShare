<?php
require_once 'config.php';

$id = $_GET['id'] ?? '';

if (array_key_exists($id, $files)) {
    $stat_file = 'download_stats.json';
    $fp = fopen($stat_file, 'c+');
    if ($fp) {
        if (flock($fp, LOCK_EX)) {
            $size = filesize($stat_file);
            $content = $size > 0 ? fread($fp, $size) : '{}';
            $stats = json_decode($content, true) ?? [];
            $stats[$id] = isset($stats[$id]) ? $stats[$id] + 1 : 1;
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($stats, JSON_PRETTY_PRINT));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
    header("Location: " . $files[$id]['url']);
    exit;
} else {
    header("Location: index.php");
    exit;
}
