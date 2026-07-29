<?php
// Ensure git is available on PATH
$gitPath = defined('GIT_CMD_PATH') ? GIT_CMD_PATH : 'C:\\Program Files\\Git\\cmd';
$envPath = getenv('PATH');
if (strpos($envPath, $gitPath) === false) {
  putenv('PATH=' . $gitPath . ';' . $envPath);
}

if (!defined('GH_REPOS_DIR')) {
  define('GH_REPOS_DIR', defined('GH_REPOS_DIR_CUSTOM') ? GH_REPOS_DIR_CUSTOM : dirname(dirname(dirname(__DIR__))) . '/repos');
}

// Load GitFren plugin functions
$pluginCore = WP_PLUGIN_DIR . '/gitfren/includes/core.php';
$pluginRouting = WP_PLUGIN_DIR . '/gitfren/includes/routing.php';
if (file_exists($pluginCore)) {
  require_once $pluginCore;
}
if (file_exists($pluginRouting)) {
  require_once $pluginRouting;
}

add_action('after_switch_theme', function() {
  flush_rewrite_rules();
});