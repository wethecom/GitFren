<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
<link rel="stylesheet" href="<?= get_template_directory_uri() ?>/style.css">
<?php wp_head(); ?>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; background: #0d1117; color: #c9d1d9; line-height: 1.5; }
a { color: #58a6ff; text-decoration: none; }
a:hover { text-decoration: underline; }
.gh-header { background: #161b22; border-bottom: 1px solid #30363d; padding: 16px 0; }
.gh-header-inner { max-width: 1280px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; gap: 16px; }
.gh-logo { color: #fff; font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.gh-logo svg { fill: #fff; }
.gh-nav a { color: #c9d1d9; font-size: 14px; padding: 4px 12px; border-radius: 6px; }
.gh-nav a:hover { background: #1c2128; color: #fff; text-decoration: none; }
.gh-container { max-width: 1280px; margin: 0 auto; padding: 24px; }
.gh-content { min-height: 60vh; }
.gh-repo-list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.gh-repo-list-header h2 { font-size: 20px; font-weight: 600; }
.gh-repo-search input { background: #0d1117; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; padding: 8px 12px; font-size: 14px; width: 300px; }
.gh-repo-search input:focus { outline: none; border-color: #58a6ff; }
.gh-repo-item { display: flex; justify-content: space-between; align-items: flex-start; padding: 16px 0; border-bottom: 1px solid #21262d; }
.gh-repo-item h3 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
.gh-repo-desc { font-size: 13px; color: #8b949e; margin-bottom: 8px; }
.gh-repo-meta { display: flex; gap: 16px; font-size: 12px; color: #8b949e; align-items: center; }
.gh-lang-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 4px; vertical-align: middle; }
.gh-repo-stars { color: #8b949e; font-size: 13px; white-space: nowrap; }
.gh-btn { background: #21262d; border: 1px solid #30363d; color: #c9d1d9; padding: 5px 16px; border-radius: 6px; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
.gh-btn:hover { background: #30363d; text-decoration: none; }
.gh-repo-nav { background: #161b22; border-bottom: 1px solid #30363d; padding: 0 24px; }
.gh-repo-nav-inner { max-width: 1280px; margin: 0 auto; display: flex; gap: 0; }
.gh-repo-nav-inner a { padding: 12px 16px; font-size: 14px; color: #8b949e; border-bottom: 2px solid transparent; }
.gh-repo-nav-inner a:hover { color: #c9d1d9; text-decoration: none; border-bottom-color: #8b949e; }
.gh-repo-nav-inner a.active { color: #c9d1d9; border-bottom-color: #f78166; }
.gh-repo-header { padding: 24px 0; }
.gh-repo-header h1 { font-size: 24px; font-weight: 600; }
.gh-repo-header .gh-meta { font-size: 13px; color: #8b949e; display: flex; gap: 16px; margin-top: 8px; }
.gh-file-list { border: 1px solid #30363d; border-radius: 6px; overflow: hidden; }
.gh-file-list-header { background: #161b22; padding: 8px 16px; font-size: 12px; color: #8b949e; display: flex; justify-content: space-between; border-bottom: 1px solid #30363d; }
.gh-file-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; border-bottom: 1px solid #21262d; font-size: 13px; }
.gh-file-item:last-child { border-bottom: none; }
.gh-file-item:hover { background: #1c2128; }
.gh-file-name { display: flex; align-items: center; gap: 8px; }
.gh-file-name svg { fill: #8b949e; flex-shrink: 0; }
.gh-file-msg { color: #8b949e; flex: 1; margin: 0 16px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.gh-file-time { color: #8b949e; white-space: nowrap; font-size: 12px; }
.gh-readme { border: 1px solid #30363d; border-radius: 6px; margin-top: 24px; }
.gh-readme h3 { background: #161b22; padding: 8px 16px; font-size: 14px; border-bottom: 1px solid #30363d; }
.gh-readme-content { padding: 16px; }
.gh-readme-content h1, .gh-readme-content h2 { border-bottom: 1px solid #21262d; padding-bottom: 4px; margin: 16px 0 8px; }
.gh-readme-content code { background: #161b22; border: 1px solid #30363d; border-radius: 3px; padding: 2px 4px; font-size: 85%; }
.gh-readme-content pre { background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 16px; overflow-x: auto; margin: 8px 0; }
.gh-code { background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 16px; overflow-x: auto; font-size: 13px; line-height: 1.45; }
.gh-footer { border-top: 1px solid #21262d; padding: 24px; text-align: center; font-size: 12px; color: #8b949e; margin-top: 40px; }
.gh-empty { text-align: center; padding: 64px 0; color: #8b949e; }
.gh-empty h2 { font-size: 24px; margin-bottom: 8px; color: #c9d1d9; }
.gh-empty p { font-size: 14px; }
.gh-breadcrumb { font-size: 14px; margin-bottom: 16px; color: #8b949e; }
.gh-breadcrumb a { color: #58a6ff; }
</style>
<script>
function ghFindFile() {
  var list = window.GH_FILE_LIST;
  if (!list || !list.length) return;
  var overlay = document.getElementById('gh-finder-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.id = 'gh-finder-overlay';
    overlay.innerHTML = '<div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:999;display:flex;justify-content:center;padding-top:80px;" onclick="if(event.target===this)ghCloseFinder()"><div style="background:#161b22;border:1px solid #30363d;border-radius:8px;width:500px;max-height:70vh;display:flex;flex-direction:column;box-shadow:0 16px 48px rgba(0,0,0,0.4);"><input id="gh-finder-input" type="text" placeholder="Go to file..." style="background:#0d1117;border:none;border-bottom:1px solid #30363d;border-radius:8px 8px 0 0;color:#c9d1d9;padding:16px;font-size:16px;outline:none;" oninput="ghFilterFiles(this.value)"><div id="gh-finder-results" style="overflow-y:auto;flex:1;"></div></div></div>';
    document.body.appendChild(overlay);
    document.getElementById('gh-finder-input').focus();
    ghFilterFiles('');
  } else {
    overlay.style.display = '';
    document.getElementById('gh-finder-input').focus();
    ghFilterFiles('');
  }
}
function ghCloseFinder() { document.getElementById('gh-finder-overlay').style.display = 'none'; }
function ghFilterFiles(q) {
  var list = window.GH_FILE_LIST || [];
  var lower = q.toLowerCase();
  var filtered = lower ? list.filter(function(f) { return f.toLowerCase().indexOf(lower) !== -1; }) : list.slice(0, 50);
  var html = '';
  filtered.slice(0, 100).forEach(function(f) {
    html += '<a href="' + f.url + '" style="display:block;padding:10px 16px;border-bottom:1px solid #21262d;font-size:14px;color:#c9d1d9;text-decoration:none;" onmouseover="this.style.background=\'#1c2128\'" onmouseout="this.style.background=\'\'">' + f.label + '</a>';
  });
  if (!html) html = '<div style="padding:24px;text-align:center;color:#8b949e;font-size:14px;">No files found</div>';
  document.getElementById('gh-finder-results').innerHTML = html;
}
document.addEventListener('keydown', function(e) {
  if (e.key === 't' && !e.ctrlKey && !e.metaKey && !e.altKey && !e.target.closest('input,textarea,select,details')) {
    ghFindFile();
  }
  if (e.key === 'Escape') ghCloseFinder();
});
</script>
</head>
<body>
<div class="gh-header">
  <div class="gh-header-inner">
    <div class="gh-logo">
      <img src="<?= get_template_directory_uri() ?>/logo.jpg" alt="GitFren" style="height:32px;width:32px;border-radius:4px;">
      <a href="<?= home_url() ?>">GitFren</a>
    </div>
    <div class="gh-nav">
      <a href="<?= home_url() ?>">Repositories</a>
      <a href="<?= home_url('/new-repo') ?>">New Repo</a>
      <a href="<?= home_url('/clone-repo') ?>">Clone Repo</a>
      <a href="<?= home_url('/user') ?>">Profile</a>
    </div>
  </div>
</div>
