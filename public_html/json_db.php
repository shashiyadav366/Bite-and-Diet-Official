<?php
/**
 * json_db.php — minimal JSON file store backed by the data/ directory.
 * Single source of truth for the whole site (database-free).
 *
 * Files live in the data/ folder next to this helper. Read results are cached
 * per request. Writes are atomic-ish: exclusive lock + truncate + single fwrite.
 */

if (defined('JSON_DB_LOADED')) {
    return;
}
define('JSON_DB_LOADED', true);

if (!isset($GLOBALS['jd_cache'])) {
    $GLOBALS['jd_cache'] = [];
}

function jd_data_dir() {
    return __DIR__ . '/data';
}

function jd_path($name) {
    $file = jd_data_dir() . '/' . basename($name);
    if (substr($file, -5) !== '.json') {
        $file .= '.json';
    }
    return $file;
}

/**
 * Read + decode a JSON data file.
 * @param string $name  file name with or without .json extension
 * @param mixed  $default returned when the file is missing/invalid
 * @return mixed
 */
function jd_read($name, $default = []) {
    $file = jd_path($name);
    if (isset($GLOBALS['jd_cache'][$file])) {
        return $GLOBALS['jd_cache'][$file];
    }
    if (!is_file($file) || filesize($file) === 0) {
        return $default;
    }
    $json = file_get_contents($file);
    if ($json === false) {
        return $default;
    }
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $default;
    }
    $GLOBALS['jd_cache'][$file] = $data;
    return $data;
}

/**
 * Write + encode a JSON data file under an exclusive lock.
 * @param string $name   file name with or without .json extension
 * @param mixed  $data   structure to encode
 * @param bool   $pretty pretty-print the file
 * @param bool   $backup copy the current file to "<file>.bak.json" before writing
 * @return bool
 */
function jd_write($name, $data, $pretty = true, $backup = false) {
    $file = jd_path($name);
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    if ($backup && is_file($file)) {
        @copy($file, $file . '.bak.json');
    }
    $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if ($pretty) {
        $flags |= JSON_PRETTY_PRINT;
    }
    $json = json_encode($data, $flags);
    if ($json === false) {
        return false;
    }
    $fh = fopen($file, 'c');
    if (!$fh) {
        return false;
    }
    flock($fh, LOCK_EX);
    ftruncate($fh, 0);
    rewind($fh);
    $ok = fwrite($fh, $json);
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    $GLOBALS['jd_cache'][$file] = $data;

    $written = $ok !== false;

    // Any successful write to a JSON-backed content file can change the sitemap,
    // so refresh it here. That covers every admin page using jd_write() (success
    // stories add/edit/delete, etc.) without needing a hook in each one.
    // sitemap_generate_if_stale() only rewrites when a source file is newer
    // than sitemap.xml, so this is cheap to call on every write.
    if ($written && is_file(__DIR__ . '/admin/sitemap_lib.php')) {
        require_once __DIR__ . '/admin/sitemap_lib.php';
        sitemap_generate_if_stale();
    }

    return $written;
}

/**
 * Next available numeric id for a JSON row list (max id + 1).
 * @param array $rows list of associative arrays
 * @param string $key column holding the numeric id
 * @return int
 */
function jd_next_id($rows, $key = 'id') {
    $max = 0;
    foreach ($rows as $row) {
        if (isset($row[$key]) && (int)$row[$key] > $max) {
            $max = (int)$row[$key];
        }
    }
    return $max + 1;
}