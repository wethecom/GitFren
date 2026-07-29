<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$tags = gh_get_tags($name);
get_header(); ?>
<?php gh_repo_tabs('tags', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div style="display:flex;gap:12px;align-items:center;">
      <h2 style="font-size:18px;">Tags</h2>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/create-tag/') ?>" class="gh-btn" style="font-size:13px;background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;">New tag</a>
    </div>
    <a href="<?= home_url('/repo/' . urlencode($name) . '/archive/' . urlencode($repo['branch']) . '.zip') ?>" class="gh-btn">
      <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;"><path d="M3.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5zM6 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5A.5.5 0 016 13zM8.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5z"/></svg>
      Download ZIP
    </a>
  </div>
  <?php if (empty($tags)): ?>
    <div class="gh-empty"><p>No tags found.</p></div>
  <?php else: ?>
    <div class="gh-file-list">
      <div class="gh-file-list-header">
        <span>Tag</span>
        <span>Message</span>
        <span>Updated</span>
      </div>
      <?php foreach ($tags as $tag): ?>
      <div class="gh-file-item">
        <div class="gh-file-name" style="flex:1;">
          <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;"><path d="M1 7.775V2.75C1 1.784 1.784 1 2.75 1h5.025c.464 0 .91.184 1.238.513l6.25 6.25a1.75 1.75 0 010 2.474l-5.026 5.026a1.75 1.75 0 01-2.474 0l-6.25-6.25A1.752 1.752 0 011 7.775zm1.5 0c0 .066.026.13.073.177l6.25 6.25a.25.25 0 00.354 0l5.025-5.025a.25.25 0 000-.354l-6.25-6.25a.25.25 0 00-.177-.073H2.75a.25.25 0 00-.25.25v5.025z"/></svg>
          <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . urlencode($tag['name'])) ?>" style="font-size:14px;"><strong><?= esc_html($tag['name']) ?></strong></a>
        </div>
        <div class="gh-file-msg" style="flex:1;">
          <a href="<?= home_url('/repo/' . urlencode($name) . '/commit/' . $tag['commit_id']) ?>" style="color:#58a6ff;font-family:monospace;"><?= $tag['commit_id_short'] ?></a>
          <?php if ($tag['message']): ?><span style="margin-left:8px;"><?= esc_html($tag['message']) ?></span><?php endif; ?>
        </div>
        <span class="gh-file-time" style="flex:0 0 auto;"><?= esc_html($tag['time_ago']) ?></span>
        <a href="<?= home_url('/repo/' . urlencode($name) . '/archive/' . urlencode($tag['name']) . '.zip') ?>" class="gh-btn" style="font-size:12px;margin-left:8px;white-space:nowrap;">
          <svg height="14" viewBox="0 0 16 16" width="14" style="fill:#8b949e;vertical-align:middle;"><path d="M3.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5zM6 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5A.5.5 0 016 13zM8.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5z"/></svg>
          ZIP
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
