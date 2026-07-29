<?php
$name = get_query_var('gh_repo');
$branch = get_query_var('gh_branch');
$path = get_query_var('gh_path');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$files = gh_get_repo_files($name, $path);
?>
<?php get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <?php gh_get_branch_selector($name, $branch, $path); ?>
    <div style="display:flex;gap:8px;align-items:center;">
      <form action="<?= home_url('/repo/' . urlencode($name) . '/search/') ?>" method="get" style="display:flex;">
        <input type="search" name="q" placeholder="Search this repository…" style="background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:5px 12px;font-size:13px;width:200px;">
      </form>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/upload/' . urlencode($branch) . '/' . urlencode($path)) ?>" class="gh-btn" title="Upload files">Upload</a>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/archive/' . urlencode($branch) . '.zip') ?>" class="gh-btn" title="Download ZIP">
        <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;"><path d="M3.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5zM6 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5A.5.5 0 016 13zM8.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5z"/></svg>
      </a>
    </div>
  </div>
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
  <?php if (empty($files)): ?>
  <div class="gh-empty">
    <p>This directory is empty.</p>
  </div>
  <?php else: ?>
  <script>GH_FILE_LIST=<?= json_encode(array_map(fn($f)=>['url'=>home_url('/repo/' . urlencode($name) . '/blob/' . $branch . '/' . $f['path']),'label'=>$f['type']==='dir'?'📁 '.$f['name']:'📄 '.$f['name']], $files)) ?>;</script>
  <div class="gh-file-list">
    <div class="gh-file-list-header">
      <span>Name</span>
      <span>Last commit message</span>
      <span>Last updated</span>
    </div>
    <?php foreach ($files as $f):
      $href = $f['type'] === 'dir'
        ? home_url('/repo/' . urlencode($name) . '/tree/' . $branch . '/' . $f['path'])
        : home_url('/repo/' . urlencode($name) . '/blob/' . $branch . '/' . $f['path']);
    ?>
    <div class="gh-file-item">
      <div class="gh-file-name">
        <?php if ($f['type'] === 'dir'): ?>
        <svg height="16" viewBox="0 0 16 16" width="16"><path d="M1.75 1A1.75 1.75 0 000 2.75v10.5C0 14.216.784 15 1.75 15h12.5A1.75 1.75 0 0016 13.25v-8.5A1.75 1.75 0 0014.25 3H7.5a.25.25 0 01-.2-.1l-.9-1.2C6.07 1.26 5.55 1 5 1H1.75z" fill="#58a6ff"/></svg>
        <?php else: ?>
        <svg height="16" viewBox="0 0 16 16" width="16"><path d="M2 1.75C2 .784 2.784 0 3.75 0h5.586c.464 0 .909.184 1.237.513l2.914 2.914c.329.328.513.773.513 1.237v9.586A1.75 1.75 0 0112.25 16h-8.5A1.75 1.75 0 012 14.25V1.75z" fill="#8b949e"/></svg>
        <?php endif; ?>
        <a href="<?= $href ?>"><?= esc_html($f['name']) ?></a>
      </div>
      <span class="gh-file-msg"><?= esc_html($f['msg']) ?></span>
      <span class="gh-file-time"><?= esc_html($f['time']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
