<?php
$name = get_query_var('gh_repo');
$base = get_query_var('gh_branch');
$head = get_query_var('gh_compare');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$diff = gh_get_diff_between($name, $base, $head);
$ahead = gh_get_compare_commits($name, $base, $head);
get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . urlencode($base)) ?>" style="font-family:monospace;font-size:16px;font-weight:600;"><?= esc_html($base) ?></a>
    <span style="color:#8b949e;">...</span>
    <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . urlencode($head)) ?>" style="font-family:monospace;font-size:16px;font-weight:600;"><?= esc_html($head) ?></a>
    <span style="font-size:14px;color:#8b949e;">
      <?php if ($diff): ?>
      <span style="color:#3fb950;">+<?= $diff['stats']['additions'] ?></span>
      <span style="color:#f85149;margin-left:4px;">-<?= $diff['stats']['deletions'] ?></span>
      in <?= $diff['stats']['files'] ?> files
      <?php endif; ?>
    </span>
  </div>

  <?php if (!empty($ahead)): ?>
  <div style="border:1px solid #30363d;border-radius:6px;overflow:hidden;margin-bottom:16px;">
    <div style="background:#161b22;padding:8px 16px;font-size:13px;color:#8b949e;border-bottom:1px solid #30363d;">Commits ahead (<?= count($ahead) ?>)</div>
    <?php foreach ($ahead as $c): ?>
    <div style="display:flex;padding:8px 16px;border-bottom:1px solid #21262d;font-size:13px;gap:12px;">
      <a href="<?= home_url('/repo/' . urlencode($name) . '/commit/' . $c['hash']) ?>" style="font-family:monospace;color:#58a6ff;"><?= $c['hash_short'] ?></a>
      <span style="color:#c9d1d9;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= esc_html($c['message']) ?></span>
      <span style="color:#8b949e;white-space:nowrap;"><?= esc_html($c['author']) ?></span>
      <span style="color:#8b949e;white-space:nowrap;"><?= esc_html($c['time_ago']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($diff && !empty($diff['files'])): ?>
    <div style="margin-bottom:12px;font-size:13px;color:#8b949e;">
      Showing <strong><?= count($diff['files']) ?></strong> changed file<?= count($diff['files']) !== 1 ? 's' : '' ?>
    </div>
    <?php $renderer = function($diff) use ($name) { ?>
      <?php foreach ($diff['files'] as $file): ?>
      <div style="border:1px solid #30363d;border-radius:6px;overflow:hidden;margin-bottom:8px;">
        <div style="background:#161b22;padding:8px 16px;font-size:13px;font-family:monospace;border-bottom:1px solid #30363d;display:flex;justify-content:space-between;">
          <span>
            <?php if ($file['name'] !== $file['old_name']): ?><span style="color:#8b949e;"><?= esc_html($file['old_name']) ?> → </span><?php endif; ?>
            <span style="color:#c9d1d9;"><?= esc_html($file['name']) ?></span>
          </span>
          <span><span style="color:#3fb950;">+<?= $file['additions'] ?></span><span style="color:#f85149;margin-left:4px;">-<?= $file['deletions'] ?></span></span>
        </div>
        <div style="font-family:monospace;font-size:12px;line-height:1.5;overflow-x:auto;">
          <?php foreach ($file['lines'] as $l):
            if ($l['type'] === 'header'): ?>
            <div style="background:#0d1117;padding:4px 16px;color:#8b949e;border-bottom:1px solid #21262d;">@@ -<?= $l['old_start'] ?> +<?= $l['new_start'] ?> @@<?php if ($l['section']): ?> <?= esc_html($l['section']) ?><?php endif; ?></div>
            <?php elseif ($l['type'] === 'add'): ?>
            <div style="background:#0b2e1a;padding:2px 16px;color:#3fb950;border-bottom:1px solid #0d2814;"><?= esc_html($l['line']) ?></div>
            <?php elseif ($l['type'] === 'del'): ?>
            <div style="background:#2d1517;padding:2px 16px;color:#f85149;border-bottom:1px solid #261417;"><?= esc_html($l['line']) ?></div>
            <?php else: ?>
            <div style="padding:2px 16px;color:#8b949e;border-bottom:1px solid #161b22;"><?= esc_html($l['line']) ?></div>
          <?php endif; endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php }; $renderer($diff); ?>
  <?php elseif ($diff): ?>
    <div class="gh-empty"><p>No differences between these branches.</p></div>
  <?php else: ?>
    <div class="gh-empty"><p>Could not load comparison.</p></div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
