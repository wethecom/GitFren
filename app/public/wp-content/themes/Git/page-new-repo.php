<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['repo_name'])) {
  $result = gh_create_repo($_POST['repo_name']);
  if ($result === true) {
    $name = preg_replace('/[^a-zA-Z0-9_.-]/', '-', basename($_POST['repo_name']));
    $name = trim($name, '-.');
    echo '<script>window.location.href = "' . home_url('/repo/' . urlencode($name)) . '";</script>';
    exit;
  }
  $error = $result;
}
get_header();
$createdName = '';
if (isset($_GET['created'])) {
  $createdName = basename($_GET['created']);
} elseif (isset($_POST['repo_name'])) {
  $n = basename($_POST['repo_name']);
  $createdName = trim(preg_replace('/[^a-zA-Z0-9_.-]/', '-', $n), '-.');
}
?>
<div class="gh-container">
  <div class="gh-repo-header">
    <h1>New Repository</h1>
  </div>
  <?php if (isset($error)): ?>
  <div style="background:#da3633;color:#fff;padding:12px 16px;border-radius:6px;margin-bottom:16px;"><?= esc_html($error) ?></div>
  <?php endif; ?>
  <form method="post" style="max-width:500px;">
    <div style="margin-bottom:16px;">
      <label style="display:block;margin-bottom:6px;font-size:14px;font-weight:600;">Repository name</label>
      <input type="text" name="repo_name" placeholder="my-repo" required pattern="[a-zA-Z0-9_.-]+" title="Only letters, numbers, dots, underscores, and hyphens allowed" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:8px 12px;font-size:14px;">
    </div>
    <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;padding:8px 16px;">Create Repository</button>
  </form>
  <div style="margin-top:24px;padding:16px;background:#161b22;border:1px solid #30363d;border-radius:6px;">
    <h3 style="font-size:14px;margin-bottom:8px;">Clone or push to your new repo:</h3>
    <pre style="background:#0d1117;padding:12px;border-radius:6px;font-size:13px;overflow-x:auto;">git clone <?= home_url('/git/' . urlencode($createdName)) ?>
cd <?= esc_html($createdName) ?>
# make changes, then:
git push</pre>
    <p style="font-size:13px;color:#8b949e;margin-top:8px;">
      Or clone via Desktop app: <code><?= home_url('/git/' . urlencode($createdName)) ?></code>
    </p>
  </div>
</div>
<?php get_footer(); ?>
