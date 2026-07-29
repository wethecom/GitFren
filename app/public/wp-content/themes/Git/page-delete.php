<?php
$name = get_query_var('gh_repo');
$branch = get_query_var('gh_branch');
$path = get_query_var('gh_path');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$fullPath = GH_REPOS_DIR . '/' . $name . '/' . $path;
if (!file_exists($fullPath)) { wp_die('File not found.', '', ['response' => 404]); }
$deleted = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    $dir = GH_REPOS_DIR . '/' . $name;
    $out = shell_exec("git -C " . escapeshellarg($dir) . " rm " . escapeshellarg($path) . " 2>&1");
    $commit = shell_exec("git -C " . escapeshellarg($dir) . " commit -m " . escapeshellarg("Delete $path") . " 2>&1");
    $deleted = true;
  } else {
    $error = 'Please confirm deletion.';
  }
}

get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<div class="gh-container">
  <?php if ($deleted): ?>
  <div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;font-size:14px;">
    File deleted. <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . $branch) ?>" style="color:#58a6ff;">Back to repository</a>
  </div>
  <?php else: ?>
  <div style="max-width:500px;margin:40px auto;text-align:center;">
    <svg height="48" viewBox="0 0 16 16" width="48" style="fill:#f85149;margin-bottom:16px;"><path d="M2.343 13.657A8 8 0 1113.657 2.343 8 8 0 012.343 13.657zM6.5 4a.5.5 0 00-.5.5v2.69L4.332 8.5 6 9.81v2.69a.5.5 0 00.5.5h3a.5.5 0 00.5-.5v-2.69l1.668-1.31L10 7.19V4.5a.5.5 0 00-.5-.5h-3z"/></svg>
    <h2 style="font-size:20px;margin-bottom:8px;">Delete <?= esc_html(basename($path)) ?>?</h2>
    <p style="font-size:14px;color:#8b949e;margin-bottom:24px;">This action will permanently remove this file from the repository.</p>
    <?php if ($error): ?>
    <div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f85149;font-size:14px;"><?= esc_html($error) ?></div>
    <?php endif; ?>
    <form method="post" style="display:flex;gap:12px;justify-content:center;">
      <a href="<?= home_url('/repo/' . urlencode($name) . '/blob/' . $branch . '/' . $path) ?>" class="gh-btn" style="padding:10px 24px;font-size:14px;">Cancel</a>
      <button type="submit" name="confirm" value="yes" class="gh-btn" style="background:#da3633;border-color:rgba(240,246,252,0.1);color:#fff;padding:10px 24px;font-size:14px;">Delete file</button>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
