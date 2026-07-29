<?php
$name = get_query_var('gh_repo');
$branch = get_query_var('gh_branch');
$path = get_query_var('gh_path');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$content = gh_get_file_content($name, $path);
if ($content === null) { wp_die('File not found.', '', ['response' => 404]); }
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
?>
<?php get_header(); ?>
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
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="font-size:18px;"><?= esc_html(basename($path)) ?></h2>
    <div style="display:flex;gap:8px;">
      <a href="<?= home_url('/repo/' . urlencode($name) . '/blame/' . $branch . '/' . $path) ?>" class="gh-btn" style="font-size:12px;">Blame</a>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/commits/' . $branch . '/?path=' . urlencode($path)) ?>" class="gh-btn" style="font-size:12px;">History</a>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/edit/' . $branch . '/' . $path) ?>" class="gh-btn" style="font-size:12px;">Edit</a>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/delete/' . $branch . '/' . $path) ?>" class="gh-btn" style="font-size:12px;color:#f85149;">Delete</a>
      <?php if (gh_is_text_file($path)): ?>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/raw/' . $branch . '/' . $path) ?>" class="gh-btn" style="font-size:12px;">Raw</a>
      <?php endif; ?>
    </div>
  </div>
  <?php if (gh_is_text_file($path)): ?>
  <div class="gh-file-list">
    <div class="gh-file-list-header" style="font-size:13px;padding:8px 16px;">
      <span><?= count(explode("\n", $content)) ?> lines</span>
      <span><?= gh_format_size(strlen($content)) ?></span>
    </div>
    <?= gh_syntax_highlight($content, $ext) ?>
  </div>
  <?php else: ?>
  <div class="gh-empty">
    <p>This file type cannot be displayed inline.</p>
  </div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
