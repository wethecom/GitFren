<?php
$name = get_query_var('gh_repo');
$branch = get_query_var('gh_branch') ?: $repo['branch'];
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 30;
$commits = gh_get_commits($name, $branch, $page, $perPage);
$totalCommits = gh_get_commit_count($name, $branch);
$totalPages = ceil($totalCommits / $perPage);
get_header(); ?>
<?php gh_repo_tabs('commits', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
    <h2 style="font-size:18px;">Commits</h2>
    <span style="font-size:13px;color:#8b949e;">
      <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;vertical-align:middle;"><path d="M11.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122V6A2.5 2.5 0 0110 8.5H6a1 1 0 00-1 1v1.128a2.251 2.251 0 11-1.5 0V5.372a2.25 2.25 0 111.5 0v1.836A2.492 2.492 0 016 7h4a1 1 0 001-1v-.628A2.25 2.25 0 019.5 3.25zM4.25 12a.75.75 0 100 1.5.75.75 0 000-1.5zM3.5 3.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0z"/></svg>
      <strong><?= esc_html($branch) ?></strong>
    </span>
    <span style="font-size:13px;color:#8b949e;"><?= number_format($totalCommits) ?> commits</span>
  </div>
  <?php if (empty($commits)): ?>
    <div class="gh-empty"><p>No commits found.</p></div>
  <?php else: ?>
    <div class="gh-file-list">
      <?php foreach ($commits as $c): ?>
      <div class="gh-file-item">
        <div class="gh-file-name">
          <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;flex-shrink:0;"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zM4.5 7.5a.5.5 0 000 1h7a.5.5 0 000-1h-7z"/></svg>
          <a href="<?= home_url('/repo/' . urlencode($name) . '/commit/' . $c['hash']) ?>" style="font-family:monospace;color:#58a6ff;font-size:13px;"><?= $c['hash_short'] ?></a>
          <span style="color:#c9d1d9;font-size:13px;margin-left:8px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= esc_html($c['message']) ?></span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
          <span style="font-size:12px;color:#8b949e;white-space:nowrap;"><?= esc_html($c['author']) ?></span>
          <span style="font-size:12px;color:#8b949e;white-space:nowrap;"><?= esc_html($c['time_ago']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($totalPages > 1): ?>
      <?php gh_pagination($page, $totalPages, home_url('/repo/' . urlencode($name) . '/commits/' . urlencode($branch) . '/?p=%d')); ?>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
