<?php
$name = get_query_var('gh_repo');
$branch = get_query_var('gh_branch');
$path = get_query_var('gh_path');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$fullPath = GH_REPOS_DIR . '/' . $name . '/' . $path;
$content = file_exists($fullPath) ? file_get_contents($fullPath) : '';
$error = '';
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = $_POST['content'] ?? '';
  $message = trim($_POST['message'] ?? '');
  if (!$message) $message = 'Update ' . basename($path);
  $dir = GH_REPOS_DIR . '/' . $name;
  $tmpf = $dir . '/.gh-tmp-' . uniqid();
  @file_put_contents($tmpf, $content);
  @rename($tmpf, $fullPath);
  $out = gh_commit_file($dir, $path, $message);
  $saved = true;
}

get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<div class="gh-container">
  <div class="gh-breadcrumb">
    <a href="<?= home_url('/repo/' . urlencode($name)) ?>"><?= esc_html($name) ?></a>
    / <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . $branch) ?>"><?= esc_html($branch) ?></a>
    <?php
    $parts = explode('/', $path);
    $cumulative = '';
    foreach ($parts as $i => $p):
      $cumulative .= ($cumulative ? '/' : '') . $p;
      if ($i < count($parts) - 1):
    ?>
    / <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . $branch . '/' . $cumulative) ?>"><?= esc_html($p) ?></a>
    <?php else: ?>
    / <strong><?= esc_html($p) ?></strong>
    <?php endif; endforeach; ?>
  </div>
  <?php if ($saved): ?>
  <div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;font-size:14px;">
    File committed. <a href="<?= home_url('/repo/' . urlencode($name) . '/blob/' . $branch . '/' . $path) ?>" style="color:#58a6ff;">View file</a>
  </div>
  <?php endif; ?>
  <form method="post">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <h2 style="font-size:18px;">Editing <?= esc_html(basename($path)) ?></h2>
      <div style="display:flex;gap:8px;">
        <a href="<?= home_url('/repo/' . urlencode($name) . '/blob/' . $branch . '/' . $path) ?>" class="gh-btn">Cancel</a>
        <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;">Commit changes</button>
      </div>
    </div>
    <div style="margin-bottom:12px;">
      <input type="text" name="message" placeholder="Commit message…" required style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
    </div>
    <textarea name="content" style="width:100%;height:500px;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:12px;font-family:monospace;font-size:13px;line-height:1.5;resize:vertical;"><?= esc_textarea($content) ?></textarea>
  </form>
</div>
<?php get_footer(); ?>
