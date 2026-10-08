<?php
/**
 * Config loader. This file holds NO secrets - it only locates and reads
 * bd_config.php, which must live outside the web root.
 *
 * Usage from anywhere in the site:
 *     require_once __DIR__ . '/app_config.php';        // or '../app_config.php'
 *     cfg('google_api_key');
 *
 * It is safe to require this file from inside a function: the values are held
 * in a function static rather than a global, so no PHP variable scope applies.
 *
 * On failure it throws, so a missing config file is obvious in the logs
 * instead of silently logging people in with empty credentials.
 */

if (!function_exists('bd_config_locate')) {

    /**
     * Walk up from the web root looking for bd_config.php.
     * Covers both layouts:
     *   C:\xampp\htdocs\public_html\..\bd_config.php
     *   /home/user/public_html/../bd_config.php
     */
    function bd_config_locate()
    {
        static $found = false;
        static $path  = null;
        if ($found) {
            return $path;
        }

        $candidates = [];

        $env = getenv('BD_CONFIG_FILE');
        if (is_string($env) && $env !== '') {
            $candidates[] = $env;
        }

        $dir = __DIR__;
        for ($i = 0; $i < 6; $i++) {
            $candidates[] = $dir . '/bd_config.php';
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                $found = true;
                $path  = $candidate;
                return $path;
            }
        }

        $found = true;
        $path  = null;
        return null;
    }

    /**
     * Load the config once, regardless of the calling scope.
     */
    function bd_config_all()
    {
        static $config = null;
        static $loaded = false;

        if ($loaded) {
            return $config;
        }
        $loaded = true;

        $path = bd_config_locate();
        if ($path === null) {
            throw new RuntimeException(
                'bd_config.php not found. It must sit one level above the web root '
                . '(e.g. /home/user/bd_config.php), or be pointed at by the '
                . 'BD_CONFIG_FILE environment variable.'
            );
        }

        $values = include $path;
        if (!is_array($values)) {
            throw new RuntimeException('bd_config.php did not return an array.');
        }

        $config = $values;
        return $config;
    }

    /**
     * Read a config value, or $default when the key is missing/empty.
     */
    function cfg($key, $default = null)
    {
        $config = bd_config_all();
        $value  = array_key_exists($key, $config) ? $config[$key] : $default;
        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * Location of the config file, for diagnostics only.
     */
    function bd_config_path()
    {
        return bd_config_locate();
    }
}