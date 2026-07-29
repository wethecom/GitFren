<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$dir = GH_REPOS_DIR . '/' . $name;
$branches = gh_get_branches($name);
$error = ''; $merged = ''; $output = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $source = trim($_POST['source'] ?? '');
  $dest = trim($_POST['dest'] ?? '');
  $message = trim($_POST['message'] ?? '');
  if (!$source || !$dest) { $error = 'Select source and destination branches.'; }
  elseif ($source === $dest) { $error = 'Source and destination must be different.'; }
  else {
    $output = gh_merge_branch($dir, $source, $dest, $message);
    if ($output && strpos($output, 'CONFLICT') !== false) { $error = 'Merge conflict! Resolve manually via CLI.'; }
    else { $merged = "$source → $dest"; }
  }
}
$ghostBranch = $_GET['merge'] ?? '';
get_header(); ?>
<?php gh_repo_tabs('branches', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <?php if ($merged): ?>
  <div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;font-size:14px;">
    Merged <strong><?= esc_html($merged) ?></strong>.
    <a href="<?= home_url('/repo/' . urlencode($name) . '/commits/' . urlencode($dest)) ?>" style="color:#58a6ff;">View commits</a>
  </div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f85149;font-size:14px;"><?= esc_html($error) ?></div>
  <?php endif; ?>
  <?php if ($output): ?>
  <pre style="background:#0d1117;border:1px solid #30363d;border-radius:6px;padding:16px;font-size:12px;color:#8b949e;margin-bottom:16px;overflow-x:auto;"><?= esc_html($output) ?></pre>
  <?php endif; ?>
  <form method="post" style="max-width:500px;">
    <h2 style="font-size:18px;margin-bottom:16px;">Merge branches</h2>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Source branch (changes from)</label>
      <select name="source" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
        <?php foreach ($branches as $b): ?>
        <option value="<?= esc_attr($b) ?>" <?= $b === $ghostBranch ? 'selected' : '' ?>><?= esc_html($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Destination branch (merge into)</label>
      <select name="dest" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
        <?php foreach ($branches as $b): ?>
        <option value="<?= esc_attr($b) ?>" <?= $b === $repo['branch'] ? 'selected' : '' ?>><?= esc_html($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Commit message (optional)</label>
      <input type="text" name="message" placeholder="Merge branch..." style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
    </div>
    <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;padding:10px 24px;font-size:14px;">Merge</button>
    <a href="<?= home_url('/repo/' . urlencode($name) . '/branches/') ?>" class="gh-btn" style="padding:10px 24px;font-size:14px;">Cancel</a>
  </form>
</div>
<?php get_footer(); ?>
