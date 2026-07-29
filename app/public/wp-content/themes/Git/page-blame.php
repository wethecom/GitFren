<?php
$name = get_query_var('gh_repo');
$branch = get_query_var('gh_branch');
$path = get_query_var('gh_path');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$content = gh_get_file_content($name, $path);
if ($content === null) { wp_die('File not found.', '', ['response' => 404]); }
$blame = gh_get_blame($name, $path, $branch);
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
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="font-size:18px;"><?= esc_html(basename($path)) ?></h2>
    <div style="display:flex;gap:8px;">
      <a href="<?= home_url('/repo/' . urlencode($name) . '/blob/' . $branch . '/' . $path) ?>" class="gh-btn" style="font-size:12px;">Source</a>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/commits/' . $branch . '/?path=' . urlencode($path)) ?>" class="gh-btn" style="font-size:12px;">History</a>
    </div>
  </div>
  <div style="border:1px solid #30363d;border-radius:6px;overflow:hidden;">
    <div style="display:flex;font-size:12px;background:#161b22;border-bottom:1px solid #30363d;">
      <div style="width:280px;min-width:280px;padding:6px 12px;color:#8b949e;border-right:1px solid #30363d;">Commit / Author</div>
      <div style="flex:1;padding:6px 12px;color:#8b949e;">Line</div>
    </div>
    <?php if (!empty($blame)): ?>
      <?php foreach ($blame as $b): ?>
      <div style="display:flex;font-family:monospace;font-size:12px;line-height:1.6;border-bottom:1px solid #21262d;">
        <div style="width:280px;min-width:280px;padding:2px 12px;background:#0d1117;border-right:1px solid #30363d;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          <a href="<?= home_url('/repo/' . urlencode($name) . '/commit/' . $b['commit']) ?>" style="color:#58a6ff;" title="<?= esc_attr($b['summary']) ?>"><?= gh_short_sha($b['commit']) ?></a>
          <span style="color:#8b949e;margin-left:6px;"><?= esc_html($b['author']) ?></span>
        </div>
        <div style="flex:1;padding:2px 12px;color:#c9d1d9;white-space:pre;overflow-x:auto;">
          <span style="color:#8b949e;margin-right:16px;user-select:none;"><?= str_pad($b['lineno'], 4, ' ', STR_PAD_LEFT) ?></span><?= esc_html($b['content']) ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <?php foreach (explode("\n", rtrim($content)) as $i => $line): ?>
      <div style="display:flex;font-family:monospace;font-size:12px;line-height:1.6;border-bottom:1px solid #21262d;">
        <div style="width:280px;min-width:280px;padding:2px 12px;background:#0d1117;border-right:1px solid #30363d;color:#8b949e;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Not yet committed</div>
        <div style="flex:1;padding:2px 12px;color:#c9d1d9;white-space:pre;">
          <span style="color:#8b949e;margin-right:16px;user-select:none;"><?= str_pad($i + 1, 4, ' ', STR_PAD_LEFT) ?></span><?= esc_html($line) ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php get_footer(); ?>
