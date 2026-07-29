<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$status = $_GET['status'] ?? 'open';
$label = $_GET['label'] ?? '';
$issues = gh_get_issues($name, $status, $label);
$closedCount = count(gh_get_issues($name, 'closed'));
get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div style="display:flex;gap:16px;align-items:center;">
      <h2 style="font-size:18px;">Issues</h2>
      <div style="display:flex;gap:4px;font-size:13px;">
        <a href="<?= home_url('/repo/' . urlencode($name) . '/issues/?status=open') ?>" style="padding:6px 12px;border-radius:6px;text-decoration:none;<?= $status === 'open' ? 'background:#1f6feb;color:#fff;' : 'color:#8b949e;' ?>">
          <svg height="16" viewBox="0 0 16 16" width="16" fill="currentColor" style="vertical-align:middle;"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zm0 2a6 6 0 100 12A6 6 0 008 2zm0 2.5a.5.5 0 01.5.5v3a.5.5 0 01-1 0V5a.5.5 0 01.5-.5z"/></svg>
          <?= count($issues) ?> Open
        </a>
        <a href="<?= home_url('/repo/' . urlencode($name) . '/issues/?status=closed') ?>" style="padding:6px 12px;border-radius:6px;text-decoration:none;<?= $status === 'closed' ? 'background:#1f6feb;color:#fff;' : 'color:#8b949e;' ?>">
          <svg height="16" viewBox="0 0 16 16" width="16" fill="currentColor" style="vertical-align:middle;"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zm0 2a6 6 0 100 12A6 6 0 008 2zm3.28 4.72a.75.75 0 00-1.06 0L7.5 9.44 5.78 7.72a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.06 0l3.25-3.25a.75.75 0 000-1.06z"/></svg>
          <?= $closedCount ?> Closed
        </a>
      </div>
    </div>
    <a href="<?= home_url('/repo/' . urlencode($name) . '/issues/new') ?>" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;padding:8px 16px;font-size:13px;text-decoration:none;">New Issue</a>
  </div>
  <?php if (empty($issues)): ?>
  <div class="gh-empty"><p>No <?= esc_html($status) ?> issues.</p></div>
  <?php else: ?>
  <div style="border:1px solid #30363d;border-radius:6px;">
    <?php foreach ($issues as $i): $labels = wp_get_object_terms($i->ID, 'gh_label'); ?>
    <div style="display:flex;align-items:center;gap:8px;padding:12px 16px;border-bottom:1px solid #30363d;">
      <a href="<?= home_url('/repo/' . urlencode($name) . '/issues/' . $i->ID) ?>" style="flex:1;color:#e6edf3;text-decoration:none;font-size:14px;">
        <svg height="16" viewBox="0 0 16 16" width="16" fill="#3fb950" style="vertical-align:middle;margin-right:4px;"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zm0 2a6 6 0 100 12A6 6 0 008 2zm0 2.5a.5.5 0 01.5.5v3a.5.5 0 01-1 0V5a.5.5 0 01.5-.5z"/></svg>
        <?= esc_html($i->post_title) ?>
        <?php foreach ($labels as $l): ?>
        <span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;background:#1f6feb;color:#fff;margin-left:4px;"><?= esc_html($l->name) ?></span>
        <?php endforeach; ?>
      </a>
      <span style="font-size:12px;color:#8b949e;white-space:nowrap;"><?= esc_html(human_time_diff(strtotime($i->post_date))) ?> ago</span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>