<?php
$name = get_query_var('gh_repo');
$branch = get_query_var('gh_branch');
$path = get_query_var('gh_path');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$dir = GH_REPOS_DIR . '/' . $name;
$error = ''; $uploaded = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['files'])) {
  $message = trim($_POST['message'] ?? 'Upload files');
  foreach ($_FILES['files']['tmp_name'] as $i => $tmp) {
    if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;
    $origName = basename($_FILES['files']['name'][$i]);
    $relPath = ($path ? $path . '/' : '') . $origName;
    $fullPath = $dir . '/' . $relPath;
    @mkdir(dirname($fullPath), 0777, true);
    if (move_uploaded_file($tmp, $fullPath)) {
      $uploaded[] = $origName;
    }
  }
  if (!empty($uploaded)) {
    $addCmd = "";
    foreach ($uploaded as $f) {
      $rp = ($path ? $path . '/' : '') . $f;
      $addCmd .= "git -C " . escapeshellarg($dir) . " add " . escapeshellarg($rp) . " 2>&1 && ";
    }
    shell_exec($addCmd . "git -C " . escapeshellarg($dir) . " commit -m " . escapeshellarg($message) . " 2>&1");
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
    / <strong>Upload files</strong>
  </div>
  <?php if (!empty($uploaded)): ?>
  <div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;font-size:14px;">
    Uploaded: <?= esc_html(implode(', ', $uploaded)) ?>.
    <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . $branch . '/' . $path) ?>" style="color:#58a6ff;">View directory</a>
  </div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f85149;font-size:14px;"><?= esc_html($error) ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" style="max-width:600px;">
    <h2 style="font-size:18px;margin-bottom:16px;">Upload files to <?= esc_html($path ?: '/') ?></h2>
    <div style="border:2px dashed #30363d;border-radius:6px;padding:40px;text-align:center;margin-bottom:16px;background:#0d1117;" id="dropzone">
      <svg height="48" viewBox="0 0 16 16" width="48" style="fill:#8b949e;margin-bottom:12px;"><path d="M3.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5zM6 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5A.5.5 0 016 13zM8.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5z"/><path d="M7.5 5.5V1.5a.5.5 0 011 0v4h4a.5.5 0 010 1h-4v4a.5.5 0 01-1 0v-4h-4a.5.5 0 010-1h4z"/></svg>
      <p style="font-size:14px;color:#c9d1d9;margin-bottom:8px;">Drag & drop files or <a href="#" onclick="document.getElementById('fileInput').click();return false;" style="color:#58a6ff;">browse</a></p>
      <p style="font-size:12px;color:#8b949e;">You can upload multiple files at once</p>
      <input type="file" name="files[]" id="fileInput" multiple style="display:none;" onchange="document.getElementById('dropzone').querySelector('p').textContent = this.files.length + ' file(s) selected'">
    </div>
    <div style="margin-bottom:16px;">
      <input type="text" name="message" placeholder="Commit message…" value="Upload files" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
    </div>
    <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;padding:10px 24px;font-size:14px;">Upload files</button>
    <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . $branch . '/' . $path) ?>" class="gh-btn" style="padding:10px 24px;font-size:14px;">Cancel</a>
  </form>
</div>
<?php get_footer(); ?>
