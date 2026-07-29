<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$error = ''; $forked = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newName = trim($_POST['new_name'] ?? '');
  if (!$newName) { $error = 'Repository name is required.'; }
  elseif (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $newName)) { $error = 'Invalid repository name. Use letters, numbers, hyphens, underscores, and dots.'; }
  else {
    $result = gh_fork_repo($name, $newName);
    if ($result === true) { $forked = $newName; }
    else { $error = $result; }
  }
}
get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <?php if ($forked): ?>
  <div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;font-size:14px;">
    Forked! <strong><a href="<?= home_url('/repo/' . urlencode($forked)) ?>" style="color:#58a6ff;"><?= esc_html($forked) ?></a></strong> created from <?= esc_html($name) ?>.
  </div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f85149;font-size:14px;"><?= esc_html($error) ?></div>
  <?php endif; ?>
  <form method="post" style="max-width:500px;">
    <h2 style="font-size:18px;margin-bottom:8px;">Fork <?= esc_html($name) ?></h2>
    <p style="font-size:14px;color:#8b949e;margin-bottom:16px;">Create a copy of this repository under a new name.</p>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">New repository name</label>
      <input type="text" name="new_name" placeholder="my-fork" required style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;font-family:monospace;">
    </div>
    <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;padding:10px 24px;font-size:14px;">Fork</button>
    <a href="<?= home_url('/repo/' . urlencode($name)) ?>" class="gh-btn" style="padding:10px 24px;font-size:14px;">Cancel</a>
  </form>
</div>
<?php get_footer(); ?>
