<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['repo_url'])) {
  $result = gh_clone_repo($_POST['repo_url']);
  if ($result === true) {
    $name = basename($_POST['repo_url'], '.git');
    echo '<script>window.location.href = "' . home_url('/repo/' . urlencode($name)) . '";</script>';
    exit;
  }
  $error = $result;
}
get_header(); ?>
<div class="gh-container">
  <div class="gh-repo-header">
    <h1>Clone Repository</h1>
  </div>
  <?php if (isset($error)): ?>
  <div style="background:#da3633;color:#fff;padding:12px 16px;border-radius:6px;margin-bottom:16px;"><?= esc_html($error) ?></div>
  <?php endif; ?>
  <form method="post" style="max-width:600px;">
    <div style="margin-bottom:16px;">
      <label style="display:block;margin-bottom:6px;font-size:14px;font-weight:600;">Remote URL</label>
      <input type="text" name="repo_url" placeholder="https://example.com/user/repo.git" required style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:8px 12px;font-size:14px;">
    </div>
    <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;padding:8px 16px;">Clone</button>
  </form>
</div>
<?php get_footer(); ?>
