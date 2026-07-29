<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$query = trim($_GET['q'] ?? '');
$results = $query ? gh_search_code($name, $query) : [];
get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <div style="margin-bottom:16px;">
    <form action="<?= home_url('/repo/' . urlencode($name) . '/search/') ?>" method="get" style="display:flex;gap:8px;">
      <input type="search" name="q" value="<?= esc_attr($query) ?>" placeholder="Search code…" style="flex:1;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 16px;font-size:14px;">
      <button type="submit" class="gh-btn" style="padding:10px 24px;font-size:14px;">Search</button>
    </form>
  </div>
  <?php if ($query): ?>
    <p style="font-size:14px;color:#8b949e;margin-bottom:16px;">
      <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> for "<strong style="color:#c9d1d9;"><?= esc_html($query) ?></strong>"
    </p>
    <?php if (empty($results)): ?>
      <div class="gh-empty"><p>No results found.</p></div>
    <?php else: ?>
      <div class="gh-file-list">
        <div class="gh-file-list-header">
          <span>File</span>
          <span>Line</span>
          <span>Content</span>
        </div>
        <?php foreach ($results as $r):
          $fileLink = home_url('/repo/' . urlencode($name) . '/blob/' . urlencode($repo['branch']) . '/' . $r['file']);
        ?>
        <div class="gh-file-item">
          <div class="gh-file-name">
            <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;"><path d="M2 1.75C2 .784 2.784 0 3.75 0h5.586c.464 0 .909.184 1.237.513l2.914 2.914c.329.328.513.773.513 1.237v9.586A1.75 1.75 0 0112.25 16h-8.5A1.75 1.75 0 012 14.25V1.75z"/></svg>
            <a href="<?= $fileLink ?>"><?= esc_html($r['file']) ?></a>
          </div>
          <span style="font-family:monospace;font-size:12px;color:#8b949e;">#<?= $r['line'] ?></span>
          <span style="font-family:monospace;font-size:12px;color:#8b949e;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:50%;"><?= esc_html($r['content']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
