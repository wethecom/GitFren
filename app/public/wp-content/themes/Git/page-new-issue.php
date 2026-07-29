<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$created = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['title'])) {
  if (!is_user_logged_in()) { $error = 'You must be logged in to create an issue.'; }
  else {
    $labels = isset($_POST['labels']) ? array_map('trim', explode(',', $_POST['labels'])) : [];
    $id = gh_create_issue($name, $_POST['title'], $_POST['body'] ?? '', $labels);
    if (is_wp_error($id)) { $error = $id->get_error_message(); }
    else { $created = true; $issueUrl = home_url('/repo/' . urlencode($name) . '/issues/' . $id); }
  }
}
get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <h2 style="font-size:18px;margin-bottom:16px;">New Issue</h2>
  <?php if ($created): ?>
  <div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;">
    Issue created. <a href="<?= $issueUrl ?>" style="color:#58a6ff;">View it</a>
  </div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f85149;"><?= esc_html($error) ?></div>
  <?php endif; ?>
  <form method="post" style="max-width:700px;">
    <div style="margin-bottom:16px;">
      <input type="text" name="title" placeholder="Issue title" required style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#e6edf3;padding:10px 12px;font-size:14px;">
    </div>
    <div style="margin-bottom:16px;">
      <textarea name="body" placeholder="Describe the issue..." rows="8" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#e6edf3;padding:10px 12px;font-size:14px;font-family:monospace;"></textarea>
    </div>
    <div style="margin-bottom:16px;">
      <input type="text" name="labels" placeholder="Labels (comma-separated, e.g. bug, enhancement)" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#e6edf3;padding:10px 12px;font-size:13px;">
    </div>
    <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;padding:10px 24px;font-size:14px;">Submit new issue</button>
  </form>
</div>
<?php get_footer(); ?>