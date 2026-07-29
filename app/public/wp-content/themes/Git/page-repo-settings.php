<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$dir = GH_REPOS_DIR . '/' . $name;
$desc = $repo['desc'];
$saved = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['confirm'])) {
  $newDesc = trim($_POST['description'] ?? '');
  $newBranch = trim($_POST['default_branch'] ?? '');
  $descFile = $dir . '/description';
  $headFile = $dir . '/.git/HEAD';
  if ($newDesc !== $repo['desc']) {
    @file_put_contents($descFile, $newDesc);
    $desc = $newDesc;
    $saved = true;
  }
  if ($newBranch && $newBranch !== $repo['branch']) {
    $branchExists = @shell_exec("git -C " . escapeshellarg($dir) . " rev-parse --verify " . escapeshellarg($newBranch) . " 2>nul");
    if ($branchExists) {
      @file_put_contents($headFile, "ref: refs/heads/$newBranch\n");
      @shell_exec("git -C " . escapeshellarg($dir) . " symbolic-ref HEAD refs/heads/" . escapeshellarg($newBranch) . " 2>nul");
      $repo['branch'] = $newBranch;
      $saved = true;
    } else {
      $error = 'Branch "' . esc_html($newBranch) . '" does not exist.';
    }
  }
  if (isset($_POST['visibility'])) {
    $v = $_POST['visibility'] === 'private' ? 'private' : 'public';
    gh_set_repo_visibility($name, $v);
    $saved = true;
  }
  if ($saved) $repo = gh_get_repo($name);
}
$branches = gh_get_branches($name);
$visibility = gh_get_repo_visibility($name);
get_header(); ?>
<?php gh_repo_tabs('settings', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container">
  <?php if ($saved): ?>
  <div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;font-size:14px;">Settings saved.</div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f85149;font-size:14px;"><?= $error ?></div>
  <?php endif; ?>
  <form method="post" style="max-width:600px;">
    <div style="margin-bottom:24px;">
      <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Description</label>
      <input type="text" name="description" value="<?= esc_attr($desc) ?>" placeholder="Short description of this repository" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
    </div>
    <div style="margin-bottom:24px;">
      <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Default branch</label>
      <select name="default_branch" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
        <?php foreach ($branches as $b): ?>
        <option value="<?= esc_attr($b) ?>" <?= $b === $repo['branch'] ? 'selected' : '' ?>><?= esc_html($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="margin-bottom:24px;">
      <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Visibility</label>
      <div style="display:flex;gap:16px;">
        <label style="font-size:14px;color:#c9d1d9;display:flex;align-items:center;gap:6px;cursor:pointer;">
          <input type="radio" name="visibility" value="public" <?= $visibility === 'public' ? 'checked' : '' ?>>
          Public
        </label>
        <label style="font-size:14px;color:#c9d1d9;display:flex;align-items:center;gap:6px;cursor:pointer;">
          <input type="radio" name="visibility" value="private" <?= $visibility === 'private' ? 'checked' : '' ?>>
          Private
        </label>
      </div>
    </div>
    <div style="font-size:13px;color:#8b949e;margin-bottom:24px;padding:12px;background:#161b22;border:1px solid #30363d;border-radius:6px;">
      <strong>Clone:</strong> <code style="font-family:monospace;"><?= home_url('/git/' . urlencode($name)) ?></code>
    </div>
    <button type="submit" class="gh-btn" style="padding:10px 24px;font-size:14px;background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;">Save changes</button>
  </form>

  <hr style="border:none;border-top:1px solid #30363d;margin:32px 0;">

  <h2 style="font-size:18px;margin-bottom:16px;">Webhooks</h2>
  <?php
  $hooks = gh_get_webhooks($name);
  $hookError = ''; $hookMsg = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['webhook_url'])) {
    $url = trim($_POST['webhook_url']);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
      gh_add_webhook($name, $url);
      $hookMsg = 'Webhook added.';
      $hooks = gh_get_webhooks($name);
    } else { $hookError = 'Invalid URL.'; }
  }
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_webhook'])) {
    gh_remove_webhook($name, $_POST['remove_webhook']);
    $hookMsg = 'Webhook removed.';
    $hooks = gh_get_webhooks($name);
  }
  if ($hookMsg): ?><div style="background:#0b2e1a;border:1px solid #3fb950;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#3fb950;font-size:14px;"><?= esc_html($hookMsg) ?></div><?php endif; ?>
  <?php if ($hookError): ?><div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f85149;font-size:14px;"><?= esc_html($hookError) ?></div><?php endif; ?>
  <?php if (empty($hooks)): ?>
  <p style="font-size:14px;color:#8b949e;margin-bottom:16px;">No webhooks configured. Webhooks fire on every push.</p>
  <?php else: ?>
  <div class="gh-file-list" style="margin-bottom:16px;">
    <?php foreach ($hooks as $hook): ?>
    <div class="gh-file-item">
      <span style="font-family:monospace;font-size:13px;"><?= esc_html($hook) ?></span>
      <form method="post" style="margin:0;">
        <input type="hidden" name="remove_webhook" value="<?= esc_attr($hook) ?>">
        <button type="submit" class="gh-btn" style="font-size:12px;color:#f85149;">Remove</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <form method="post" style="display:flex;gap:8px;max-width:500px;">
    <input type="url" name="webhook_url" placeholder="https://example.com/webhook" style="flex:1;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 12px;font-size:14px;">
    <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;">Add webhook</button>
  </form>
</div>

<hr style="border:none;border-top:1px solid #30363d;margin:32px 0;">

<h2 style="font-size:18px;margin-bottom:16px;color:#f85149;">Danger Zone</h2>
<div style="padding:16px;border:1px solid #f85149;border-radius:6px;max-width:600px;">
  <p style="font-size:14px;color:#c9d1d9;margin-bottom:12px;">Once you delete this repository, there is no going back. Please be certain.</p>
  <form method="post" action="<?= home_url('/repo/' . urlencode($name) . '/delete-repo') ?>" onsubmit="return confirm('Are you sure you want to permanently delete this repository? This action cannot be undone.');">
    <input type="hidden" name="confirm" value="1">
    <button type="submit" style="padding:10px 24px;font-size:14px;background:#da3633;border:1px solid #f85149;border-radius:6px;color:#fff;cursor:pointer;">Delete this repository</button>
  </form>
</div>
<?php get_footer(); ?>