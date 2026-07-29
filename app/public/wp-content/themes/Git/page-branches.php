<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$branches = gh_get_branches($name);
get_header(); ?>
<?php gh_repo_tabs('branches', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div style="display:flex;gap:12px;align-items:center;">
      <h2 style="font-size:18px;">Branches</h2>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/create-branch/') ?>" class="gh-btn" style="font-size:13px;background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;">New branch</a>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/merge/') ?>" class="gh-btn" style="font-size:13px;">Merge branches</a>
    </div>
    <a href="<?= home_url('/repo/' . urlencode($name) . '/archive/' . urlencode($repo['branch']) . '.zip') ?>" class="gh-btn">
      <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;"><path d="M3.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5zM6 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5A.5.5 0 016 13zM8.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5z"/></svg>
      Download ZIP
    </a>
  </div>
  <?php if (empty($branches)): ?>
    <div class="gh-empty"><p>No branches found.</p></div>
  <?php else: ?>
    <div class="gh-file-list">
      <div class="gh-file-list-header">
        <span>Branch</span>
        <span>Last commit</span>
        <span>Updated</span>
      </div>
      <?php foreach ($branches as $branch):
        $info = gh_get_branch_info($name, $branch);
      ?>
      <div class="gh-file-item">
        <div class="gh-file-name">
          <svg height="16" viewBox="0 0 16 16" width="16"><path fill="#58a6ff" d="M11.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122V6A2.5 2.5 0 0110 8.5H6a1 1 0 00-1 1v1.128a2.251 2.251 0 11-1.5 0V5.372a2.25 2.25 0 111.5 0v1.836A2.492 2.492 0 016 7h4a1 1 0 001-1v-.628A2.25 2.25 0 019.5 3.25zM4.25 12a.75.75 0 100 1.5.75.75 0 000-1.5zM3.5 3.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0z"/></svg>
          <strong style="font-size:14px;">
            <?php if ($info): ?>
            <a href="<?= home_url('/repo/' . urlencode($name) . '/tree/' . urlencode($branch)) ?>"><?= esc_html($branch) ?></a>
            <?php else: ?>
            <?= esc_html($branch) ?>
            <?php endif; ?>
          </strong>
        </div>
        <div class="gh-file-msg">
          <?php if ($info): ?>
          <a href="<?= home_url('/repo/' . urlencode($name) . '/commit/' . $info['commit_id']) ?>" style="color:#58a6ff;font-family:monospace;"><?= $info['commit_id_short'] ?></a>
          <span style="margin-left:8px;"><?= esc_html($info['message']) ?></span>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <?php if ($branch !== $repo['branch']): ?>
          <a href="<?= home_url('/repo/' . urlencode($name) . '/merge/?merge=' . urlencode($branch)) ?>" class="gh-btn" style="font-size:11px;">Merge</a>
          <?php endif; ?>
          <span class="gh-file-time"><?= $info ? esc_html($info['time']) : '' ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
