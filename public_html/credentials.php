<?php
/**
 * Database credentials now live in bd_config.php, outside the web root.
 * This file is kept as a thin shim so existing admin pages that do
 * require_once '../credentials.php' keep working unchanged.
 */
require_once __DIR__ . '/app_config.php';

$dbHost     = cfg('db_host');
$dbName     = cfg('db_name');
$dbUsername = cfg('db_user');
$dbPassword = cfg('db_password');