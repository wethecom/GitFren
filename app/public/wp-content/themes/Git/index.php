<?php get_header(); ?>
<div class="gh-content">
  <div class="gh-container">
    <div style="display:flex;gap:40px;">
      <div style="flex:1;">
        <div class="gh-repo-list-header">
          <h2>Repositories</h2>
          <div class="gh-repo-search">
            <input type="text" id="gh-search" placeholder="Find a repository..." oninput="filterRepos(this.value)">
          </div>
        </div>
        <div class="gh-repo-list" id="gh-repo-list">
          <?php
          $repos = gh_get_repos();
          if (empty($repos)):
          ?>
          <div class="gh-empty">
            <h2>No repositories yet</h2>
            <p><a href="<?= home_url('/new-repo') ?>" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;margin-top:12px;">Create a new repository</a></p>
            <p style="margin-top:8px;">Or <a href="<?= home_url('/clone-repo') ?>">clone an existing repository</a></p>
          </div>
          <?php else:
          foreach ($repos as $repo):
            $name = $repo['name'];
            $desc = $repo['desc'];
            $lang = $repo['lang'];
            $updated = $repo['updated'];
            $stars = $repo['stars'];
          ?>
          <div class="gh-repo-item" data-name="<?= strtolower($name) ?>">
            <div class="gh-repo-info">
              <h3><a href="<?= home_url('/repo/' . urlencode($name)) ?>"><?= esc_html($name) ?></a></h3>
              <p class="gh-repo-desc"><?= esc_html($desc) ?: 'No description' ?></p>
              <div class="gh-repo-meta">
                <?php if ($lang): ?>
                <span class="gh-lang"><span class="gh-lang-dot" style="background:<?= gh_lang_color($lang) ?>"></span><?= esc_html($lang) ?></span>
                <?php endif; ?>
                <span>Updated <?= esc_html($updated) ?></span>
              </div>
            </div>
            <div class="gh-repo-stars" style="display:flex;align-items:center;gap:12px;">
              <a href="<?= home_url('/repo/' . urlencode($name) . '/star?op=' . ($stars > 0 ? 'remove' : 'add')) ?>" style="font-size:16px;color:<?= $stars > 0 ? '#e3b341' : '#8b949e' ?>;text-decoration:none;">&#9733;</a>
              <span style="font-size:12px;color:#8b949e;"><?= $stars ?></span>
              <span style="font-size:12px;color:#8b949e;"><?= home_url('/git/' . urlencode($name)) ?></span>
              <a href="github-windows://openRepo/<?= home_url('/git/' . urlencode($name)) ?>" title="Open in GitHub Desktop" style="font-size:13px;color:#8b949e;white-space:nowrap;">Open in Desktop</a>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
      <div style="width:320px;min-width:320px;">
        <div style="border:1px solid #30363d;border-radius:6px;overflow:hidden;">
          <div style="background:#161b22;padding:12px 16px;font-size:14px;font-weight:600;border-bottom:1px solid #30363d;">Recent activity</div>
          <?php
          $activity = gh_get_recent_commits(10);
          if (empty($activity)): ?>
          <div style="padding:24px;color:#8b949e;text-align:center;font-size:13px;">No recent activity</div>
          <?php else: foreach ($activity as $c): ?>
          <div style="padding:10px 16px;border-bottom:1px solid #21262d;font-size:13px;">
            <div style="display:flex;gap:8px;">
              <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;flex-shrink:0;margin-top:2px;"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zM4.5 7.5a.5.5 0 000 1h7a.5.5 0 000-1h-7z"/></svg>
              <div style="overflow:hidden;">
                <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                  <a href="<?= home_url('/repo/' . urlencode($c['repo_name']) . '/commit/' . $c['hash']) ?>" style="color:#c9d1d9;"><?= esc_html($c['message']) ?></a>
                </div>
                <div style="color:#8b949e;font-size:12px;margin-top:2px;">
                  <a href="<?= home_url('/repo/' . urlencode($c['repo_name'])) ?>" style="color:#58a6ff;"><?= esc_html($c['repo_name']) ?></a>
                  <span style="margin:0 4px;">·</span>
                  <?= esc_html($c['time_ago']) ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php get_footer(); ?>
<script>
function filterRepos(val) {
  document.querySelectorAll('.gh-repo-item').forEach(el => {
    el.style.display = el.dataset.name.includes(val.toLowerCase()) ? '' : 'none';
  });
}
</script>
