<?php
$name = get_query_var('gh_repo');
$branch = get_query_var('gh_branch');
$path = get_query_var('gh_path');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$error = '';
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $filename = trim($_POST['filename'] ?? '');
  $content = $_POST['content'] ?? '';
  $message = trim($_POST['message'] ?? '');
  if (!$filename) { $error = 'Filename is required.'; }
  elseif (!$message) { $error = 'Commit message is required.'; }
  else {
    $dir = GH_REPOS_DIR . '/' . $name;
    $fullPath = $dir . ($path ? '/' . $path : '') . '/' . $filename;
    if (file_exists($fullPath)) { $error = 'File already exists.'; }
    else {
      $relPath = ($path ? $path . '/' : '') . $filename;
      @mkdir(dirname($fullPath), 0777, true);
      @file_put_contents($fullPath, $content);
      $out = gh_commit_file($dir, $relPath, $message);
      $saved = true;
      $savedPath = $relPath;
    }
  }
}

get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<div class="gh-container">
  <div class="gh-breadcrumb">
    <a href="<?= home_url('/repo/' . urlencode($name)) ?>"><?= esc_html($name) ?></a>
    / <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . $branch) ?>"><?= esc_html($branch) ?></a>
    <?php if ($path):
      $parts = explode('/', $path);
      $cumulative = '';
      foreach ($parts as $i => $p):
        $cumulative .= ($cumulative ? '/' : '') . $p;
    ?>
    / <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . $branch . '/' . $cumulative) ?>"><?= esc_html($p) ?></a>
    <?php endforeach;
    endif; ?>
    / <strong>New file</strong>
  </div>
  <?php if ($saved): ?>
  <div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;font-size:14px;">
    File created. <a href="<?= home_url('/repo/' . urlencode($name) . '/blob/' . $branch . '/' . $savedPath) ?>" style="color:#58a6ff;">View file</a>
  </div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f85149;font-size:14px;"><?= esc_html($error) ?></div>
  <?php endif; ?>
  <form method="post">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <h2 style="font-size:18px;">New file</h2>
      <div style="display:flex;gap:8px;">
        <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . $branch . '/' . $path) ?>" class="gh-btn">Cancel</a>
        <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;">Commit new file</button>
      </div>
    </div>
    <div style="display:flex;gap:12px;margin-bottom:12px;">
      <input type="text" name="filename" placeholder="Filename (e.g. example.php)" required style="flex:1;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;font-family:monospace;">
    </div>
    <div style="margin-bottom:12px;">
      <input type="text" name="message" placeholder="Commit message…" required style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
    </div>
    <textarea name="content" placeholder="File content…" style="width:100%;height:400px;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:12px;font-family:monospace;font-size:13px;line-height:1.5;resize:vertical;"></textarea>
  </form>
</div>
<?php get_footer(); ?>
