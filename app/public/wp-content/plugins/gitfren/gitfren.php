<?php
/**
 * Plugin Name: GitFren
 * Description: Self-hosted Git repository manager with full web UI. Browse, clone, push, and manage repos through WordPress.
 * Version: 1.0
 * Author: GitFren
 * Text Domain: gitfren
 */

defined('ABSPATH') || exit;

define('GITFREN_VERSION', '1.0');
define('GITFREN_FILE', __FILE__);
define('GITFREN_DIR', __DIR__);

if (!defined('GH_REPOS_DIR')) {
  $custom = defined('GH_REPOS_DIR_CUSTOM') ? GH_REPOS_DIR_CUSTOM : '';
  if ($custom && is_dir($custom)) {
    define('GH_REPOS_DIR', $custom);
  } elseif (is_dir(__DIR__ . '/../../repos')) {
    define('GH_REPOS_DIR', realpath(__DIR__ . '/../../repos'));
  } else {
    $uploads = wp_upload_dir();
    define('GH_REPOS_DIR', $uploads['basedir'] . '/gitfren-repos');
  }
}

register_activation_hook(__FILE__, 'gitfren_activate');
register_deactivation_hook(__FILE__, 'gitfren_deactivate');

function gitfren_activate() {
  $dir = GH_REPOS_DIR;
  if (!is_dir($dir)) wp_mkdir_p($dir);
  gitfren_add_rewrite_rules();
  flush_rewrite_rules();
}

function gitfren_deactivate() {
  flush_rewrite_rules();
}

if (!function_exists('gh_get_repos')) {
  require GITFREN_DIR . '/includes/core.php';
  require GITFREN_DIR . '/includes/routing.php';
}
require GITFREN_DIR . '/includes/settings.php';