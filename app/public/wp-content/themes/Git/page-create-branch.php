<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$dir = GH_REPOS_DIR . '/' . $name;
$branches = gh_get_branches($name);
$error = ''; $created = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $branchName = trim($_POST['branch_name'] ?? '');
  $from = trim($_POST['from'] ?? '');
  if (!$branchName) { $error = 'Branch name is required.'; }
  elseif (!preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $branchName)) { $error = 'Invalid branch name.'; }
  else {
    $result = gh_create_branch($dir, $branchName, $from ?: 'HEAD');
    if (strpos($result ?? '', 'fatal:') !== false) { $error = $result; }
    else { $created = $branchName; }
  }
}
get_header(); ?>
<?php gh_repo_tabs('branches', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <?php if ($created): ?>
  <div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;font-size:14px;">
    Branch <strong><?= esc_html($created) ?></strong> created.
    <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . urlencode($created)) ?>" style="color:#58a6ff;">Browse</a>
  </div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f85149;font-size:14px;"><?= esc_html($error) ?></div>
  <?php endif; ?>
  <form method="post" style="max-width:500px;">
    <h2 style="font-size:18px;margin-bottom:16px;">Create branch</h2>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Branch name</label>
      <input type="text" name="branch_name" placeholder="e.g. feature-xyz" required style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;font-family:monospace;">
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">From</label>
      <select name="from" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
        <option value="">HEAD (current)</option>
        <?php foreach ($branches as $b): ?>
        <option value="<?= esc_attr($b) ?>" <?= $b === $repo['branch'] ? 'selected' : '' ?>><?= esc_html($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;padding:10px 24px;font-size:14px;">Create branch</button>
    <a href="<?= home_url('/repo/' . urlencode($name) . '/branches/') ?>" class="gh-btn" style="padding:10px 24px;font-size:14px;">Cancel</a>
  </form>
</div>
<?php get_footer(); ?>
