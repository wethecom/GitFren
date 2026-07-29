<?php
defined('ABSPATH') || exit;

function gitfren_add_rewrite_rules() {
  add_rewrite_rule('^repo/([^/]+)/?$', 'index.php?gh_repo=$matches[1]', 'top');
  add_rewrite_rule('^repo/([^/]+)/blob/([^/]+)/(.+?)/?$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_path=$matches[3]', 'top');
  add_rewrite_rule('^repo/([^/]+)/tree/([^/]+)/(.+?)/?$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_path=$matches[3]', 'top');
  add_rewrite_rule('^repo/([^/]+)/branches/?$', 'index.php?gh_repo=$matches[1]&gh_action=branches', 'top');
  add_rewrite_rule('^repo/([^/]+)/commits/([^/]+)/?$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_action=commits', 'top');
  add_rewrite_rule('^repo/([^/]+)/commit/([a-f0-9]+)/?$', 'index.php?gh_repo=$matches[1]&gh_commit=$matches[2]&gh_action=commit', 'top');
  add_rewrite_rule('^repo/([^/]+)/tags/?$', 'index.php?gh_repo=$matches[1]&gh_action=tags', 'top');
  add_rewrite_rule('^repo/([^/]+)/search/?$', 'index.php?gh_repo=$matches[1]&gh_action=search', 'top');
  add_rewrite_rule('^repo/([^/]+)/blame/([^/]+)/(.+?)/?$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_path=$matches[3]&gh_action=blame', 'top');
  add_rewrite_rule('^repo/([^/]+)/archive/([^/]+)\.zip$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_action=archive', 'top');
  add_rewrite_rule('^repo/([^/]+)/settings/?$', 'index.php?gh_repo=$matches[1]&gh_action=settings', 'top');
  add_rewrite_rule('^repo/([^/]+)/raw/([^/]+)/(.+?)/?$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_path=$matches[3]&gh_action=raw', 'top');
  add_rewrite_rule('^repo/([^/]+)/edit/([^/]+)/(.+?)/?$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_path=$matches[3]&gh_action=edit', 'top');
  add_rewrite_rule('^repo/([^/]+)/delete/([^/]+)/(.+?)/?$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_path=$matches[3]&gh_action=delete', 'top');
  add_rewrite_rule('^repo/([^/]+)/compare/([^/]+)\.\.\.([^/]+)/?$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_compare=$matches[3]&gh_action=compare', 'top');
  add_rewrite_rule('^repo/([^/]+)/star/?$', 'index.php?gh_repo=$matches[1]&gh_action=star', 'top');
  add_rewrite_rule('^repo/([^/]+)/create-branch/?$', 'index.php?gh_repo=$matches[1]&gh_action=create_branch', 'top');
  add_rewrite_rule('^repo/([^/]+)/create-tag/?$', 'index.php?gh_repo=$matches[1]&gh_action=create_tag', 'top');
  add_rewrite_rule('^repo/([^/]+)/merge/?$', 'index.php?gh_repo=$matches[1]&gh_action=merge', 'top');
  add_rewrite_rule('^repo/([^/]+)/upload/([^/]+)/(.*?)/?$', 'index.php?gh_repo=$matches[1]&gh_branch=$matches[2]&gh_path=$matches[3]&gh_action=upload', 'top');
  add_rewrite_rule('^repo/([^/]+)/fork/?$', 'index.php?gh_repo=$matches[1]&gh_action=fork', 'top');
  add_rewrite_rule('^user/?$', 'index.php?gh_action=user', 'top');
  add_rewrite_rule('^new-repo/?$', 'index.php?gh_action=new_repo', 'top');
  add_rewrite_rule('^clone-repo/?$', 'index.php?gh_action=clone_repo', 'top');
  add_rewrite_rule('^repo/([^/]+)/delete-repo/?$', 'index.php?gh_repo=$matches[1]&gh_action=delete_repo', 'top');
  add_rewrite_rule('^repo/([^/]+)/issues/?$', 'index.php?gh_repo=$matches[1]&gh_action=issues', 'top');
  add_rewrite_rule('^repo/([^/]+)/issues/new/?$', 'index.php?gh_repo=$matches[1]&gh_action=new_issue', 'top');
  add_rewrite_rule('^repo/([^/]+)/issues/(\d+)/?$', 'index.php?gh_repo=$matches[1]&gh_issue_id=$matches[2]&gh_action=issue', 'top');
  add_rewrite_rule('^repo/([^/]+)/issues/(\d+)/close/?$', 'index.php?gh_repo=$matches[1]&gh_issue_id=$matches[2]&gh_action=close_issue', 'top');
  add_rewrite_rule('^repo/([^/]+)/issues/(\d+)/reopen/?$', 'index.php?gh_repo=$matches[1]&gh_issue_id=$matches[2]&gh_action=reopen_issue', 'top');
}

add_action('init', function() {
  gitfren_add_rewrite_rules();
});

add_filter('query_vars', function($vars) {
  $vars[] = 'gh_repo';
  $vars[] = 'gh_branch';
  $vars[] = 'gh_path';
  $vars[] = 'gh_commit';
  $vars[] = 'gh_action';
  $vars[] = 'gh_query';
  $vars[] = 'gh_compare';
  $vars[] = 'gh_issue_id';
  return $vars;
});

add_filter('redirect_canonical', function($redirect_url, $requested_url) {
  if (preg_match('#/repo/[^/]+/(blob|tree|blame|archive|raw|edit|delete|new-file|compare|star|create-branch|create-tag|merge|upload|fork)/#', $requested_url)) return false;
  if (preg_match('#/user/?$#', $requested_url)) return false;
  if (preg_match('#/git/#', $requested_url)) return false;
  return $redirect_url;
}, 10, 2);

// --- Git Smart HTTP handler ---

function gitfren_handle_smart_http() {
  $uri = $_SERVER['REQUEST_URI'] ?? '';
  $uri = preg_replace('#\?.*$#', '', $uri);
  $base = parse_url(home_url('/'), PHP_URL_PATH);
  $base = rtrim($base, '/');
  if (!preg_match('#^' . preg_quote($base, '#') . '/git/(.+)$#', $uri, $m)) return false;
  $route = $m[1];
  status_header(200);

  $parts = explode('/', $route, 2);
  $repoName = $parts[0] ?? '';
  $service = $parts[1] ?? '';

  if (empty($repoName)) {
    header('HTTP/1.1 404 Not Found');
    exit('Repository not specified');
  }

  $repoDir = GH_REPOS_DIR . '/' . $repoName;
  if (!is_dir($repoDir) && !is_dir($repoDir . '.git')) {
    header('HTTP/1.1 404 Not Found');
    exit('Repository not found');
  }

  if (is_dir($repoDir) && is_file($repoDir . '/HEAD') && !is_dir($repoDir . '/.git')) {
    $gitDir = $repoDir;
  } elseif (is_dir($repoDir . '/.git')) {
    $gitDir = $repoDir . '/.git';
  } else {
    $gitDir = $repoDir;
  }

  $queryService = $_GET['service'] ?? '';

  if ($service === 'info/refs') {
    if ($queryService === 'git-upload-pack') {
      $cmd = 'git upload-pack --stateless-rpc --advertise-refs';
      $contentType = 'application/x-git-upload-pack-advertisement';
    } elseif ($queryService === 'git-receive-pack') {
      $cmd = 'git receive-pack --stateless-rpc --advertise-refs';
      $contentType = 'application/x-git-receive-pack-advertisement';
    } else {
      header('HTTP/1.1 400 Bad Request');
      exit('Unknown service: ' . $queryService);
    }
    $cmd .= ' ' . escapeshellarg($gitDir);
    list($ok, $out, $err) = gitfren_run_git($cmd);
    if (!$ok) {
      header('HTTP/1.1 500 Internal Server Error');
      exit('Git error: ' . $err);
    }
    header('Content-Type: ' . $contentType);
    header('Expires: Fri, 01 Jan 1980 00:00:00 GMT');
    header('Pragma: no-cache');
    header('Cache-Control: no-cache, max-age=0, must-revalidate');
    $serviceHeader = ($queryService === 'git-upload-pack')
      ? "# service=git-upload-pack\n"
      : "# service=git-receive-pack\n";
    $pktLen = strlen($serviceHeader) + 4;
    $pktLine = sprintf('%04x', $pktLen) . $serviceHeader;
    echo $pktLine . '0000' . $out;
    exit;
  }

  if ($service === 'git-upload-pack') {
    $input = file_get_contents('php://input');
    if (empty($input)) $input = '0000';
    $cmd = 'git upload-pack --stateless-rpc ' . escapeshellarg($gitDir);
    list($ok, $out, $err) = gitfren_run_git($cmd, $input);
    if (!$ok) {
      header('HTTP/1.1 500 Internal Server Error');
      exit('Git error: ' . $err);
    }
    header('Content-Type: application/x-git-upload-pack-result');
    echo $out;
    exit;
  }

  if ($service === 'git-receive-pack') {
    $input = file_get_contents('php://input');
    $cmd = 'git receive-pack --stateless-rpc ' . escapeshellarg($gitDir);
    list($ok, $out, $err) = gitfren_run_git($cmd, $input);
    if (!$ok) {
      header('HTTP/1.1 500 Internal Server Error');
      echo 'git-receive-pack error: ' . $err;
      exit;
    }
    header('Content-Type: application/x-git-receive-pack-result');
    echo $out;
    exit;
  }

  header('HTTP/1.1 404 Not Found');
  exit('Unknown git service');
}

function gitfren_run_git($cmd, $input = null) {
  $inFile = null;
  if ($input !== null && strlen($input) > 0) {
    $inFile = tempnam(sys_get_temp_dir(), 'git_in');
    file_put_contents($inFile, $input);
  }
  $outFile = tempnam(sys_get_temp_dir(), 'git_out');
  $errFile = tempnam(sys_get_temp_dir(), 'git_err');

  $descriptorspec = [
    0 => $inFile !== null ? ['file', $inFile, 'r'] : ['pipe', 'r'],
    1 => ['file', $outFile, 'w'],
    2 => ['file', $errFile, 'w']
  ];
  $process = @proc_open($cmd, $descriptorspec, $pipes);
  if (!is_resource($process)) {
    @unlink($inFile); @unlink($outFile); @unlink($errFile);
    return [false, '', ''];
  }
  if ($inFile === null && isset($pipes[0])) @fclose($pipes[0]);
  $rc = proc_close($process);
  $output = @file_get_contents($outFile);
  if ($output === false) $output = '';
  $err = @file_get_contents($errFile);
  @unlink($inFile); @unlink($outFile); @unlink($errFile);
  return [$rc === 0, $output, $err];
}

// --- Helper functions ---

function gh_get_blame($name, $file, $branch = 'HEAD') {
  $dir = GH_REPOS_DIR . '/' . $name;
  $out = @shell_exec("git -C " . escapeshellarg($dir) . " blame --line-porcelain " . escapeshellarg($branch) . " -- " . escapeshellarg($file) . " 2>nul");
  if (!$out) return [];
  $lines = [];
  $current = null;
  foreach (explode("\n", $out) as $line) {
    if (preg_match('/^([a-f0-9]{40}) (\d+) (\d+)( \d+)?$/', $line, $m)) {
      if ($current) $lines[] = $current;
      $current = ['commit' => $m[1], 'orig_lineno' => $m[2], 'lineno' => $m[3], 'content' => '', 'author' => '', 'author_mail' => '', 'time' => '', 'summary' => ''];
    } elseif ($current && preg_match('/^author (.+)/', $line, $m)) {
      $current['author'] = $m[1];
    } elseif ($current && preg_match('/^author-mail (.+)/', $line, $m)) {
      $current['author_mail'] = trim($m[1], '<>');
    } elseif ($current && preg_match('/^author-time (\d+)/', $line, $m)) {
      $current['time'] = $m[1];
    } elseif ($current && preg_match('/^summary (.+)/', $line, $m)) {
      $current['summary'] = $m[1];
    } elseif ($current && preg_match('/^\t(.+)/', $line, $m)) {
      $current['content'] = $m[1];
      $lines[] = $current;
      $current = null;
    }
  }
  return $lines;
}

function gh_create_branch($dir, $name, $from = '') {
  $from = $from ?: 'HEAD';
  return shell_exec("git -C " . escapeshellarg($dir) . " branch " . escapeshellarg($name) . " " . escapeshellarg($from) . " 2>&1");
}

function gh_create_tag($dir, $name, $from = '', $message = '') {
  $from = $from ?: 'HEAD';
  if ($message) {
    return shell_exec("git -C " . escapeshellarg($dir) . " tag -a " . escapeshellarg($name) . " -m " . escapeshellarg($message) . " " . escapeshellarg($from) . " 2>&1");
  }
  return shell_exec("git -C " . escapeshellarg($dir) . " tag " . escapeshellarg($name) . " " . escapeshellarg($from) . " 2>&1");
}

function gh_merge_branch($dir, $source, $dest, $message = '') {
  $checkout = shell_exec("git -C " . escapeshellarg($dir) . " checkout " . escapeshellarg($dest) . " 2>&1");
  $msg = $message ?: "Merge branch '$source' into $dest";
  $merge = shell_exec("git -C " . escapeshellarg($dir) . " merge " . escapeshellarg($source) . " --no-edit -m " . escapeshellarg($msg) . " 2>&1");
  return $merge;
}

function gh_fork_repo($name, $newName) {
  $src = GH_REPOS_DIR . '/' . $name;
  $dst = GH_REPOS_DIR . '/' . $newName;
  if (!is_dir($src)) return 'Source repository not found.';
  if (is_dir($dst)) return 'Destination already exists.';
  $copy = shell_exec("git clone --no-hardlinks " . escapeshellarg($src) . " " . escapeshellarg($dst) . " 2>&1");
  if (!$copy) return 'Failed to fork repository.';
  @shell_exec("git -C " . escapeshellarg($dst) . " config receive.denyCurrentBranch ignore 2>nul");
  gh_setup_post_receive_hook($dst);
  return true;
}

function gh_setup_post_receive_hook($dir) {
  $hooksDir = $dir . '/.git/hooks';
  @mkdir($hooksDir, 0777, true);
  $webhooks = gh_get_webhooks(basename($dir));
  $hookScript = "#!/bin/sh\ncd \"$(dirname \"$0\")/../..\" || exit 1\nunset GIT_DIR\ngit reset --hard HEAD --\ngit update-server-info\n";
  foreach ($webhooks as $url) {
    $hookScript .= "curl -s -o /dev/null " . escapeshellarg($url) . " 2>/dev/null &\n";
  }
  @file_put_contents($hooksDir . '/post-receive', $hookScript);
}

function gh_get_webhooks($name) {
  $f = GH_REPOS_DIR . '/' . $name . '/.git/gh-webhooks.json';
  if (!file_exists($f)) return [];
  $data = json_decode(file_get_contents($f), true);
  return is_array($data) ? $data : [];
}

function gh_add_webhook($name, $url) {
  $hooks = gh_get_webhooks($name);
  if (in_array($url, $hooks)) return false;
  $hooks[] = $url;
  @file_put_contents(GH_REPOS_DIR . '/' . $name . '/.git/gh-webhooks.json', json_encode($hooks, JSON_PRETTY_PRINT));
  gh_setup_post_receive_hook(GH_REPOS_DIR . '/' . $name);
  return true;
}

function gh_remove_webhook($name, $url) {
  $hooks = gh_get_webhooks($name);
  $hooks = array_values(array_filter($hooks, fn($h) => $h !== $url));
  @file_put_contents(GH_REPOS_DIR . '/' . $name . '/.git/gh-webhooks.json', json_encode($hooks, JSON_PRETTY_PRINT));
  gh_setup_post_receive_hook(GH_REPOS_DIR . '/' . $name);
  return true;
}

function gh_get_branch_selector($name, $currentBranch, $currentPath = '') {
  $branches = gh_get_branches($name);
  ?>
  <div style="position:relative;display:inline-block;">
    <details style="position:relative;">
      <summary class="gh-btn" style="cursor:pointer;list-style:none;display:inline-flex;align-items:center;gap:4px;">
        <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;"><path d="M11.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122V6A2.5 2.5 0 0110 8.5H6a1 1 0 00-1 1v1.128a2.251 2.251 0 11-1.5 0V5.372a2.25 2.25 0 111.5 0v1.836A2.492 2.492 0 016 7h4a1 1 0 001-1v-.628A2.25 2.25 0 019.5 3.25z"/></svg>
        <span style="font-weight:600;"><?= esc_html($currentBranch) ?></span>
        <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;"><path d="M4.427 6.427l3.396 3.396a.25.25 0 00.354 0l3.396-3.396A.25.25 0 0011.396 6H4.604a.25.25 0 00-.177.427z"/></svg>
      </summary>
      <div style="position:absolute;top:100%;left:0;z-index:100;background:#161b22;border:1px solid #30363d;border-radius:6px;min-width:240px;max-height:320px;overflow-y:auto;margin-top:4px;box-shadow:0 8px 24px rgba(0,0,0,0.4);">
        <?php foreach ($branches as $b):
          $url = $currentPath
            ? home_url('/repo/' . urlencode($name) . '/tree/' . urlencode($b) . '/' . $currentPath)
            : home_url('/repo/' . urlencode($name) . '/tree/' . urlencode($b));
        ?>
        <a href="<?= $url ?>" style="display:block;padding:8px 16px;font-size:14px;color:<?= $b === $currentBranch ? '#58a6ff' : '#c9d1d9' ?>;border-bottom:1px solid #21262d;<?= $b === $currentBranch ? 'font-weight:600;' : '' ?>"><?= esc_html($b) ?></a>
        <?php endforeach; ?>
      </div>
    </details>
  </div>
  <?php
}

// --- Template redirect ---

add_action('template_redirect', function() {
  $uri = preg_replace('#\?.*$#', '', $_SERVER['REQUEST_URI'] ?? '');

  // Route Git Smart HTTP requests
  $parsed = parse_url(home_url('/'), PHP_URL_PATH);
  $base = rtrim($parsed, '/');
  if (preg_match('#^' . preg_quote($base, '#') . '/git/#', $uri)) {
    gitfren_handle_smart_http();
    exit;
  }

  // Manual URL routing for rewrite rules that don't match
  if (preg_match('#/repo/([^/]+)/new-file/([^/]+)(?:/(.*))?$#', $uri, $m)) {
    set_query_var('gh_repo', $m[1]);
    set_query_var('gh_branch', $m[2]);
    set_query_var('gh_path', $m[3] ?? '');
    set_query_var('gh_action', 'new');
  }
  if (preg_match('#/repo/([^/]+)/upload/([^/]+)(?:/(.*))?$#', $uri, $m)) {
    set_query_var('gh_repo', $m[1]);
    set_query_var('gh_branch', $m[2]);
    set_query_var('gh_path', $m[3] ?? '');
    set_query_var('gh_action', 'upload');
  }

  $action = get_query_var('gh_action');
  $repo = get_query_var('gh_repo');

  if ($action === 'new_repo') {
    include get_template_directory() . '/page-new-repo.php';
    exit;
  }
  if ($action === 'user') {
    include get_template_directory() . '/page-user.php';
    exit;
  }
  if ($action === 'clone_repo') {
    include get_template_directory() . '/page-clone-repo.php';
    exit;
  }

  if (!$repo) return;
  $name = $repo;

  gh_require_repo_access($name);

  if ($action === 'delete_repo') {
    if (!is_user_logged_in()) { wp_die('You must be logged in to delete a repository.', '', ['response' => 403]); }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirm'])) { wp_redirect(home_url('/repo/' . urlencode($name) . '/settings')); exit; }
    $result = gh_delete_repo($name);
    if ($result === true) {
      wp_redirect(home_url('/'));
      exit;
    }
    wp_die($result, '', ['response' => 500]);
  }

  if ($action === 'branches' || $action === 'commits' || $action === 'commit' || $action === 'tags' || $action === 'search' || $action === 'blame' || $action === 'edit' || $action === 'new' || $action === 'delete' || $action === 'delete_repo' || $action === 'compare' || $action === 'settings' || $action === 'create_branch' || $action === 'create_tag' || $action === 'merge' || $action === 'upload' || $action === 'fork' || $action === 'issues' || $action === 'new_issue' || $action === 'issue' || $action === 'close_issue' || $action === 'reopen_issue') {
    global $wp_query;
    $wp_query->is_404 = false;
    status_header(200);
  }
  if ($action === 'branches') { include get_template_directory() . '/page-branches.php'; exit; }
  if ($action === 'commits') { include get_template_directory() . '/page-commits.php'; exit; }
  if ($action === 'commit') { include get_template_directory() . '/page-commit.php'; exit; }
  if ($action === 'tags') { include get_template_directory() . '/page-tags.php'; exit; }
  if ($action === 'search') { include get_template_directory() . '/page-search.php'; exit; }
  if ($action === 'blame') { include get_template_directory() . '/page-blame.php'; exit; }
  if ($action === 'archive') {
    $branch = get_query_var('gh_branch') ?: 'HEAD';
    $dir = GH_REPOS_DIR . '/' . $name;
    if (!is_dir($dir)) { wp_die('Repository not found.', '', ['response' => 404]); }
    $tmpf = tempnam(sys_get_temp_dir(), 'gh_') . '.zip';
    $cwd = getcwd();
    chdir($dir);
    $cmd = sprintf('git archive --format=zip -o %s %s', escapeshellarg($tmpf), escapeshellarg($branch));
    @shell_exec($cmd);
    chdir($cwd);
    $size = @filesize($tmpf);
    if (!$size) { wp_die('Could not generate archive. Branch "' . esc_html($branch) . '" may not exist.', '', ['response' => 500]); }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $name . '-' . $branch . '.zip"');
    header('Content-Length: ' . $size);
    readfile($tmpf);
    @unlink($tmpf);
    exit;
  }
  if ($action === 'settings') { include get_template_directory() . '/page-repo-settings.php'; exit; }
  if ($action === 'raw') {
    $branch = get_query_var('gh_branch') ?: 'HEAD';
    $path = get_query_var('gh_path');
    $fullPath = GH_REPOS_DIR . '/' . $name . '/' . $path;
    if (!file_exists($fullPath) || is_dir($fullPath)) { wp_die('File not found.', '', ['response' => 404]); }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = ['md' => 'text/markdown', 'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json', 'xml' => 'application/xml', 'svg' => 'image/svg+xml', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'ico' => 'image/x-icon', 'pdf' => 'application/pdf', 'zip' => 'application/zip', 'woff2' => 'font/woff2', 'woff' => 'font/woff', 'ttf' => 'font/ttf', 'eot' => 'application/vnd.ms-fontobject', 'map' => 'application/json'];
    $ct = $mime[$ext] ?? (gh_is_text_file($path) ? 'text/plain' : 'application/octet-stream');
    header('Content-Type: ' . $ct);
    if (!gh_is_text_file($path) || !in_array($ext, ['md', 'css', 'js', 'json', 'xml', 'svg'])) {
      header('Content-Length: ' . filesize($fullPath));
    }
    readfile($fullPath);
    exit;
  }
  if ($action === 'edit') { include get_template_directory() . '/page-edit.php'; exit; }
  if ($action === 'new') { include get_template_directory() . '/page-new-file.php'; exit; }
  if ($action === 'delete') { include get_template_directory() . '/page-delete.php'; exit; }
  if ($action === 'compare') { include get_template_directory() . '/page-compare.php'; exit; }
  if ($action === 'star') {
    $count = gh_get_stars($name);
    $op = $_GET['op'] ?? 'add';
    if ($op === 'add') $count++; else $count = max(0, $count - 1);
    gh_set_stars($name, $count);
    wp_redirect(home_url('/repo/' . urlencode($name)));
    exit;
  }
  if ($action === 'issues') { include get_template_directory() . '/page-issues.php'; exit; }
  if ($action === 'new_issue') { include get_template_directory() . '/page-new-issue.php'; exit; }
  if ($action === 'issue') { include get_template_directory() . '/page-issue.php'; exit; }
  if ($action === 'close_issue') {
    if (!is_user_logged_in()) wp_die('Login required.', '', ['response' => 403]);
    $id = (int)get_query_var('gh_issue_id');
    gh_close_issue($id);
    wp_redirect(home_url('/repo/' . urlencode($name) . '/issues/' . $id));
    exit;
  }
  if ($action === 'reopen_issue') {
    if (!is_user_logged_in()) wp_die('Login required.', '', ['response' => 403]);
    $id = (int)get_query_var('gh_issue_id');
    gh_reopen_issue($id);
    wp_redirect(home_url('/repo/' . urlencode($name) . '/issues/' . $id));
    exit;
  }
  if ($action === 'create_branch') { include get_template_directory() . '/page-create-branch.php'; exit; }
  if ($action === 'create_tag') { include get_template_directory() . '/page-create-tag.php'; exit; }
  if ($action === 'merge') { include get_template_directory() . '/page-merge.php'; exit; }
  if ($action === 'upload') { include get_template_directory() . '/page-upload.php'; exit; }
  if ($action === 'fork') { include get_template_directory() . '/page-fork.php'; exit; }

  $path = get_query_var('gh_path');
  if ($path) {
    $fullPath = GH_REPOS_DIR . '/' . $name . '/' . $path;
    if (is_file($fullPath)) {
      include get_template_directory() . '/single-file.php';
      exit;
    }
    include get_template_directory() . '/single-dir.php';
    exit;
  }
  include get_template_directory() . '/single-repo.php';
  exit;
});

add_action('wp_enqueue_scripts', function() {
  wp_dequeue_style('wp-block-library');
  wp_dequeue_style('wp-block-library-theme');
  wp_dequeue_style('global-styles');
  wp_dequeue_style('classic-theme-styles');
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('wp_print_styles', 'print_emoji_styles');
}, 100);

add_action('after_switch_theme', function() {
  flush_rewrite_rules();
});