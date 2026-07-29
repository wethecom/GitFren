<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$files = gh_get_repo_files($name);
$latest = gh_get_commits($name, $repo['branch'], 1, 1);
$latestCommit = $latest[0] ?? null;
?>
<?php get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <?php if (empty($files)): ?>
  <div class="gh-empty">
    <h2>Empty repository</h2>
    <p>This repository is empty.</p>
  </div>
  <?php else: ?>
  <?php if ($latestCommit): ?>
  <div style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:#161b22;border:1px solid #30363d;border-radius:6px;margin-bottom:12px;font-size:13px;">
    <svg height="16" viewBox="0 0 16 16" width="16" fill="#8b949e"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zm0 2a6 6 0 100 12A6 6 0 008 2zm0 1.5a.5.5 0 01.5.5v4a.5.5 0 01-.5.5H4.5a.5.5 0 010-1h3V4a.5.5 0 01.5-.5z"/></svg>
    <a href="<?= home_url('/repo/' . urlencode($name) . '/commit/' . $latestCommit['hash']) ?>" style="font-family:monospace;font-size:12px;color:#58a6ff;text-decoration:none;"><?= substr($latestCommit['hash'], 0, 7) ?></a>
    <a href="<?= home_url('/repo/' . urlencode($name) . '/commit/' . $latestCommit['hash']) ?>" style="color:#e6edf3;text-decoration:none;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= esc_html($latestCommit['message']) ?></a>
    <span style="color:#8b949e;white-space:nowrap;"><?= esc_html($latestCommit['author']) ?></span>
    <span style="color:#8b949e;white-space:nowrap;"><?= esc_html($latestCommit['time_ago']) ?></span>
  </div>
  <?php endif; ?>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <?php gh_get_branch_selector($name, $repo['branch']); ?>
    <div style="display:flex;align-items:center;gap:8px;">
      <button onclick="ghFindFile()" class="gh-btn" style="font-size:13px;" title="Go to file (t)">Find file</button>
      <span style="font-size:13px;color:#8b949e;">
        <a href="<?= home_url('/repo/' . urlencode($name) . '/branches/') ?>"><?= count($repo['branches']) ?> branches</a>
        <span style="margin:0 8px;">·</span>
        <a href="<?= home_url('/repo/' . urlencode($name) . '/tags/') ?>">tags</a>
      </span>
      <form action="<?= home_url('/repo/' . urlencode($name) . '/search/') ?>" method="get" style="display:flex;">
        <input type="search" name="q" placeholder="Search…" style="background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:5px 12px;font-size:13px;width:160px;">
      </form>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/commits/' . urlencode($repo['branch'])) ?>" class="gh-btn">
        <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;vertical-align:middle;"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zM4.5 7.5a.5.5 0 000 1h7a.5.5 0 000-1h-7z"/></svg>
        Commits
      </a>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/archive/' . urlencode($repo['branch']) . '.zip') ?>" class="gh-btn" title="Download ZIP">
        <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;vertical-align:middle;"><path d="M3.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5zM6 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5A.5.5 0 016 13zM8.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5zM3.5 6.5a.5.5 0 01-.5-.5v-2a.5.5 0 01.5-.5H5a.5.5 0 010 1H4v1.5a.5.5 0 01-.5.5z"/><path d="M2.5 2.5h11v11h-11z" fill="none"/></svg>
      </a>
    </div>
  </div>
  <div class="gh-file-list">
    <div class="gh-file-list-header">
      <span>Name</span>
      <span>Last commit message</span>
      <span>Last updated</span>
    </div>
    <?php foreach ($files as $f):
      $href = $f['type'] === 'dir'
        ? home_url('/repo/' . urlencode($name) . '/tree/' . $repo['branch'] . '/' . $f['path'])
        : home_url('/repo/' . urlencode($name) . '/blob/' . $repo['branch'] . '/' . $f['path']);
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
  <script>GH_FILE_LIST=<?= json_encode(array_map(fn($f)=>['url'=>home_url('/repo/' . urlencode($name) . '/blob/' . $repo['branch'] . '/' . $f['path']),'label'=>$f['type']==='dir'?'📁 '.$f['name']:'📄 '.$f['name']], $files)) ?>;</script>
  <?php
  $readme = '';
  foreach (['README.md', 'readme.md', 'README.txt', 'Readme.md'] as $rm) {
    $c = gh_get_file_content($name, $rm);
    if ($c !== null) { $readme = $c; break; }
  }
  if ($readme):
  ?>
  <div class="gh-readme">
    <h3>README.md</h3>
    <div class="gh-readme-content">
      <?= gh_parse_markdown($readme) ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
