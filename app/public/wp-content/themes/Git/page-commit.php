<?php
$name = get_query_var('gh_repo');
$sha = get_query_var('gh_commit');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$commit = gh_get_commit($name, $sha);
if (!$commit) { wp_die('Commit not found.', '', ['response' => 404]); }
$diff = gh_get_diff($name, $sha);
get_header(); ?>
<?php gh_repo_tabs('commits', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <div style="border:1px solid #30363d;border-radius:6px;overflow:hidden;margin-bottom:16px;">
    <div style="background:#161b22;padding:16px 24px;">
      <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:8px;">
        <svg height="20" viewBox="0 0 16 16" width="20" style="fill:#8b949e;flex-shrink:0;margin-top:2px;"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zM4.5 7.5a.5.5 0 000 1h7a.5.5 0 000-1h-7z"/></svg>
        <div>
          <h3 style="font-size:16px;font-weight:600;margin-bottom:4px;"><?= esc_html($commit['message_title']) ?></h3>
          <?php if ($commit['message_body']): ?>
          <pre style="font-size:13px;color:#8b949e;white-space:pre-wrap;margin-bottom:8px;"><?= esc_html($commit['message_body']) ?></pre>
          <?php endif; ?>
        </div>
      </div>
      <div style="display:flex;gap:16px;font-size:13px;color:#8b949e;flex-wrap:wrap;">
        <span><strong style="color:#c9d1d9;"><?= esc_html($commit['author']) ?></strong> authored <?= esc_html($commit['time_ago']) ?></span>
        <?php if ($commit['committer'] !== $commit['author']): ?>
        <span>committed by <strong style="color:#c9d1d9;"><?= esc_html($commit['committer']) ?></strong></span>
        <?php endif; ?>
        <span>commit <code style="font-family:monospace;background:#0d1117;padding:2px 6px;border-radius:3px;border:1px solid #30363d;"><?= $commit['hash'] ?></code></span>
        <?php if (!empty($commit['parents'])): ?>
        <span>
          parent<?= count($commit['parents']) > 1 ? 's' : '' ?>:
          <?php foreach ($commit['parents'] as $p): ?>
          <a href="<?= home_url('/repo/' . urlencode($name) . '/commit/' . $p) ?>" style="font-family:monospace;"><?= gh_short_sha($p) ?></a>
          <?php endforeach; ?>
        </span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($diff && !empty($diff['files'])): ?>
  <div style="margin-bottom:12px;font-size:13px;color:#8b949e;">
    <span><?= $diff['stats']['files'] ?> files changed</span>,
    <span style="color:#3fb950;">++<?= $diff['stats']['additions'] ?></span>,
    <span style="color:#f85149;">--<?= $diff['stats']['deletions'] ?></span>
  </div>
  <?php foreach ($diff['files'] as $file): ?>
    <div style="border:1px solid #30363d;border-radius:6px;overflow:hidden;margin-bottom:8px;">
      <div style="background:#161b22;padding:8px 16px;font-size:13px;font-family:monospace;border-bottom:1px solid #30363d;display:flex;justify-content:space-between;">
        <span>
          <?php if ($file['name'] !== $file['old_name']): ?>
          <span style="color:#8b949e;"><?= esc_html($file['old_name']) ?> → </span>
          <?php endif; ?>
          <span style="color:#c9d1d9;"><?= esc_html($file['name']) ?></span>
        </span>
        <span>
          <span style="color:#3fb950;">+<?= $file['additions'] ?></span>
          <span style="color:#f85149;margin-left:4px;">-<?= $file['deletions'] ?></span>
        </span>
      </div>
      <div style="font-family:monospace;font-size:12px;line-height:1.5;overflow-x:auto;">
        <?php foreach ($file['lines'] as $l): ?>
          <?php if ($l['type'] === 'header'): ?>
            <div style="background:#0d1117;padding:4px 16px;color:#8b949e;border-bottom:1px solid #21262d;">
              <?= esc_html(preg_replace('/@@.*?@@/', '', $l['line'])) ?>
              <span style="margin-left:8px;">@@ -<?= $l['old_start'] ?> +<?= $l['new_start'] ?> @@</span>
              <?php if ($l['section']): ?><span style="color:#8b949e;"> <?= esc_html($l['section']) ?></span><?php endif; ?>
            </div>
          <?php elseif ($l['type'] === 'add'): ?>
            <div style="background:#0b2e1a;padding:2px 16px;color:#3fb950;border-bottom:1px solid #0d2814;"><?= esc_html($l['line']) ?></div>
          <?php elseif ($l['type'] === 'del'): ?>
            <div style="background:#2d1517;padding:2px 16px;color:#f85149;border-bottom:1px solid #261417;"><?= esc_html($l['line']) ?></div>
          <?php else: ?>
            <div style="padding:2px 16px;color:#8b949e;border-bottom:1px solid #161b22;"><?= esc_html($l['line']) ?></div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <?php elseif ($diff): ?>
    <div class="gh-empty"><p>No file changes in this commit.</p></div>
  <?php else: ?>
    <div class="gh-empty"><p>Could not load diff.</p></div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
