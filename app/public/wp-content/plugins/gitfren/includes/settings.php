<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function() {
  add_options_page('GitFren Settings', 'GitFren', 'manage_options', 'gitfren', 'gitfren_settings_page');
});

function gitfren_settings_page() {
  if (!current_user_can('manage_options')) wp_die('Access denied');

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gh_repos_dir'])) {
    $dir = sanitize_text_field(wp_unslash($_POST['gh_repos_dir']));
    $dir = rtrim($dir, '/\\');
    if (!is_dir($dir)) {
      echo '<div class="notice notice-error"><p>Directory does not exist: ' . esc_html($dir) . '</p></div>';
    } else {
      update_option('gitfren_repos_dir', $dir);
      if (!defined('GH_REPOS_DIR_CUSTOM')) {
        // Let the constant override but store the option
      }
      echo '<div class="notice notice-success"><p>Settings saved. Define <code>GH_REPOS_DIR_CUSTOM</code> in wp-config.php to override.</p></div>';
    }
  }

  $current = get_option('gitfren_repos_dir', GH_REPOS_DIR);
  $repos = gh_get_repos();
  ?>
  <div class="wrap">
    <h1>GitFren Settings</h1>
    <form method="post" style="margin-top:20px;">
      <table class="form-table">
        <tr>
          <th scope="row"><label for="gh_repos_dir">Repositories directory</label></th>
          <td>
            <input type="text" id="gh_repos_dir" name="gh_repos_dir" value="<?= esc_attr($current) ?>" class="regular-text" style="font-family:monospace;">
            <p class="description">Current: <code><?= esc_html(GH_REPOS_DIR) ?></code></p>
            <p class="description">To override, define <code>define('GH_REPOS_DIR_CUSTOM', '/path/to/repos');</code> in <code>wp-config.php</code>.</p>
          </td>
        </tr>
      </table>
      <p><button type="submit" class="button button-primary">Save</button></p>
    </form>

    <hr style="margin:24px 0;">
    <h2>Repositories (<?= count($repos) ?>)</h2>
    <?php if (empty($repos)): ?>
      <p>No repositories found in <code><?= esc_html(GH_REPOS_DIR) ?></code>.</p>
    <?php else: ?>
      <table class="wp-list-table widefat fixed striped">
        <thead><tr><th>Name</th><th>Stars</th><th>Language</th><th>Updated</th></tr></thead>
        <tbody>
        <?php foreach ($repos as $r): ?>
          <tr>
            <td><a href="<?= home_url('/repo/' . urlencode($r['name'])) ?>"><?= esc_html($r['name']) ?></a></td>
            <td><?= $r['stars'] ?></td>
            <td><?= esc_html($r['lang']) ?></td>
            <td><?= esc_html($r['updated']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
  <?php
}