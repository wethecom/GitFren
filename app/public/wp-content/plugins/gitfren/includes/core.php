<?php
defined('ABSPATH') || exit;

function gh_is_git_repo($dir) {
  return is_dir($dir . '/.git') || is_file($dir . '/HEAD');
}

function gh_get_repos() {
  $repos = [];
  if (!is_dir(GH_REPOS_DIR)) return $repos;
  $items = glob(GH_REPOS_DIR . '/*');
  sort($items);
  foreach ($items as $dir) {
    if (!is_dir($dir)) continue;
    if (!gh_is_git_repo($dir)) continue;
    $isBare = is_file($dir . '/HEAD') && !is_dir($dir . '/.git');
    $name = $isBare ? basename($dir, '.git') : basename($dir);
    $desc = '';
    $lang = '';
    $updated = 'unknown';
    $stars = 0;
    if ($isBare) {
      $gitDesc = $dir . '/description';
      if (file_exists($gitDesc)) {
        $d = trim(file_get_contents($gitDesc));
        if ($d && strpos($d, 'Unnamed repository') === false) $desc = $d;
      }
    } else {
      $gitDesc = $dir . '/.git/description';
      if (file_exists($gitDesc)) {
        $d = trim(file_get_contents($gitDesc));
        if ($d && strpos($d, 'Unnamed repository') === false) $desc = $d;
      }
    }
    $log = @shell_exec("git -C " . escapeshellarg($dir) . " log -1 --format=%cr 2>nul");
    if ($log) $updated = trim(trim($log, "'"));
    $lang_info = @shell_exec("git -C " . escapeshellarg($dir) . " log -1 --format= --name-only 2>nul");
    $exts = [];
    if ($lang_info) {
      $files = explode("\n", trim($lang_info));
      foreach ($files as $f) {
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if ($ext) $exts[$ext] = ($exts[$ext] ?? 0) + 1;
      }
    }
    $lang = gh_ext_to_lang($exts);
    $stars = gh_get_stars($name);
    $repos[] = compact('name', 'desc', 'lang', 'updated', 'stars');
  }
  return $repos;
}

function gh_get_repo($name) {
  $name = urldecode($name);
  $dir = GH_REPOS_DIR . '/' . $name;
  if (!is_dir($dir) || !is_dir($dir . '/.git')) return null;
  $desc = '';
  $updated = 'unknown';
  $stars = 0;
  $descFile = $dir . '/description';
  if (file_exists($descFile)) {
    $desc = trim(file_get_contents($descFile));
  }
  if (!$desc || $desc === 'Unnamed repository') {
    $gitDesc = $dir . '/.git/description';
    if (file_exists($gitDesc)) {
      $d = trim(file_get_contents($gitDesc));
      if ($d && strpos($d, 'Unnamed repository') === false) {
        $desc = $d;
      }
    }
  }
  $head = @file_get_contents($dir . '/.git/HEAD');
  preg_match('#ref: refs/heads/(.+)#', $head, $m);
  $branch = $m[1] ?? 'main';
  $log = @shell_exec("git -C " . escapeshellarg($dir) . " log -1 --format=%cr 2>nul");
  if ($log) $updated = trim($log);
  $size = @shell_exec("git -C " . escapeshellarg($dir) . " count-objects --human-readable 2>nul");
  $branches = explode("\n", trim(@shell_exec("git -C " . escapeshellarg($dir) . " branch -a 2>nul") ?? ''));
  $stars = gh_get_stars($name);
  return compact('name', 'desc', 'updated', 'stars', 'branch', 'size', 'branches');
}

function gh_get_repo_files($name, $path = '') {
  $name = urldecode($name);
  $dir = GH_REPOS_DIR . '/' . $name;
  if (!is_dir($dir)) return [];
  $fullPath = $dir . ($path ? '/' . $path : '');
  $items = [];
  $dirs = glob($fullPath . '/*', GLOB_ONLYDIR);
  sort($dirs);
  foreach ($dirs as $d) {
    $bn = basename($d);
    if ($bn === '.git') continue;
    $rel = $path ? $path . '/' . $bn : $bn;
    $items[] = ['name' => $bn, 'path' => $rel, 'type' => 'dir', 'size' => '', 'msg' => gh_last_commit_msg($dir, $rel), 'time' => gh_last_commit_time($dir, $rel)];
  }
  $files = glob($fullPath . '/*');
  sort($files);
  foreach ($files as $f) {
    if (is_dir($f)) continue;
    $bn = basename($f);
    $rel = $path ? $path . '/' . $bn : $bn;
    $size = filesize($f);
    $items[] = ['name' => $bn, 'path' => $rel, 'type' => 'file', 'size' => gh_format_size($size), 'msg' => gh_last_commit_msg($dir, $rel), 'time' => gh_last_commit_time($dir, $rel)];
  }
  return $items;
}

function gh_get_file_content($name, $path) {
  $name = urldecode($name);
  $file = GH_REPOS_DIR . '/' . $name . '/' . $path;
  if (!file_exists($file) || is_dir($file)) return null;
  return file_get_contents($file);
}

function gh_last_commit_msg($repoDir, $relPath) {
  $msg = @shell_exec("git -C " . escapeshellarg($repoDir) . " log -1 --format=%s -- " . escapeshellarg($relPath) . " 2>nul");
  return $msg ? trim($msg) : '';
}

function gh_last_commit_time($repoDir, $relPath) {
  $t = @shell_exec("git -C " . escapeshellarg($repoDir) . " log -1 --format=%cr -- " . escapeshellarg($relPath) . " 2>nul");
  return $t ? trim($t) : '';
}

function gh_format_size($bytes) {
  if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
  if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
  return $bytes . ' B';
}

function gh_ext_to_lang($exts) {
  $map = ['php' => 'PHP', 'js' => 'JavaScript', 'ts' => 'TypeScript', 'py' => 'Python', 'java' => 'Java', 'cs' => 'C#', 'cpp' => 'C++', 'c' => 'C', 'rb' => 'Ruby', 'go' => 'Go', 'rs' => 'Rust', 'swift' => 'Swift', 'kt' => 'Kotlin', 'scala' => 'Scala', 'html' => 'HTML', 'css' => 'CSS', 'scss' => 'SCSS', 'less' => 'Less', 'sql' => 'SQL', 'sh' => 'Shell', 'ps1' => 'PowerShell', 'md' => 'Markdown', 'json' => 'JSON', 'xml' => 'XML', 'yaml' => 'YAML', 'yml' => 'YAML', 'toml' => 'TOML'];
  if (empty($exts)) return '';
  arsort($exts);
  $top = array_key_first($exts);
  return $map[$top] ?? $top;
}

function gh_lang_color($lang) {
  $colors = ['PHP' => '#4F5D95', 'JavaScript' => '#f1e05a', 'Python' => '#3572A5', 'Java' => '#b07219', 'C#' => '#178600', 'C++' => '#f34b7d', 'C' => '#555', 'Ruby' => '#701516', 'Go' => '#00ADD8', 'Rust' => '#dea584', 'HTML' => '#e34c26', 'CSS' => '#563d7c', 'Shell' => '#89e051', 'PowerShell' => '#012456', 'TypeScript' => '#3178c6', 'Markdown' => '#083fa1', 'JSON' => '#292929'];
  return $colors[$lang] ?? '#666';
}

function gh_is_text_file($path) {
  $textExts = ['php', 'js', 'ts', 'py', 'java', 'cs', 'cpp', 'c', 'h', 'rb', 'go', 'rs', 'swift', 'kt', 'html', 'css', 'scss', 'less', 'sql', 'sh', 'ps1', 'md', 'json', 'xml', 'yaml', 'yml', 'toml', 'txt', 'cfg', 'ini', 'conf', 'gitignore', 'gitattributes', 'env', 'bat', 'ps1', 'rb', 'pl', 'lua', 'vue', 'svelte', 'jsx', 'tsx', 'mjs', 'cjs', 'm4', 'ac', 'am', 'in', 'map', 'svg'];
  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  return in_array($ext, $textExts);
}

function gh_syntax_highlight($code, $ext) {
  return '<pre class="gh-code"><code>' . esc_html($code) . '</code></pre>';
}

function gh_parse_markdown($md) {
  $html = '';
  $lines = explode("\n", $md);
  $inCode = false;
  $codeBlock = '';
  $codeLang = '';
  $inList = false;
  $listType = '';
  foreach ($lines as $line) {
    if (preg_match('/^```(\w*)/', $line, $m)) {
      if ($inCode) {
        $html .= '<pre><code>' . esc_html(rtrim($codeBlock)) . '</code></pre>';
        $codeBlock = ''; $codeLang = ''; $inCode = false;
      } else {
        $inCode = true; $codeLang = $m[1];
      }
      continue;
    }
    if ($inCode) { $codeBlock .= $line . "\n"; continue; }
    $trimmed = trim($line);
    if (preg_match('/^(#{1,6})\s+(.+)/', $line, $m)) {
      $html .= gh_close_list($inList, $listType);
      $inList = false;
      $n = strlen($m[1]);
      $html .= "<h$n>" . gh_inline_markdown($m[2]) . "</h$n>" . "\n";
    }
    elseif (preg_match('/^(\*|-|\+)\s+(.*)/', $line, $m)) {
      if (!$inList || $listType !== 'ul') {
        $html .= $inList ? gh_close_list($inList, $listType) : '';
        $html .= "<ul>\n"; $inList = true; $listType = 'ul';
      }
      $html .= '<li>' . gh_inline_markdown($m[2]) . "</li>\n";
    }
    elseif (preg_match('/^\d+\.\s+(.*)/', $line, $m)) {
      if (!$inList || $listType !== 'ol') {
        $html .= $inList ? gh_close_list($inList, $listType) : '';
        $html .= "<ol>\n"; $inList = true; $listType = 'ol';
      }
      $html .= '<li>' . gh_inline_markdown($m[1]) . "</li>\n";
    }
    elseif ($trimmed === '') {
      $html .= gh_close_list($inList, $listType);
      $inList = false;
    }
    elseif (preg_match('/^###+$/', $trimmed)) {
      $html .= gh_close_list($inList, $listType);
      $inList = false;
      $html .= '<hr>' . "\n";
    }
    else {
      $html .= gh_close_list($inList, $listType);
      $inList = false;
      $html .= '<p>' . gh_inline_markdown($line) . "</p>\n";
    }
  }
  if ($inCode) $html .= '<pre><code>' . esc_html(rtrim($codeBlock)) . '</code></pre>';
  $html .= gh_close_list($inList, $listType);
  return $html;
}

function gh_close_list($inList, $type) {
  if (!$inList) return '';
  return ($type === 'ol' ? "</ol>\n" : "</ul>\n");
}

function gh_inline_markdown($text) {
  $text = esc_html($text);
  $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
  $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
  $text = preg_replace('/`(.+?)`/', '<code>$1</code>', $text);
  $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
  return $text;
}

function gh_create_repo($name) {
  $name = basename($name);
  $name = preg_replace('/[^a-zA-Z0-9_.-]/', '-', $name);
  $name = trim($name, '-.');
  if (empty($name) || $name === '.git') return 'Invalid repository name';
  $dir = GH_REPOS_DIR . '/' . $name;
  if (is_dir($dir)) return 'Repository already exists';
  $cmd = sprintf('git init %s 2>&1 && git -C %s commit --allow-empty -m "Initial commit" 2>&1', escapeshellarg($dir), escapeshellarg($dir));
  $out = @shell_exec($cmd);
  if (!$out) return 'Failed to create repository';
  @shell_exec(sprintf('git -C %s config receive.denyCurrentBranch ignore 2>nul', escapeshellarg($dir)));
  gh_setup_post_receive_hook($dir);
  return true;
}

function gh_delete_repo($name) {
  $name = basename($name);
  $dir = GH_REPOS_DIR . '/' . $name;
  if (!is_dir($dir)) return 'Repository not found';
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
  foreach ($it as $f) {
    if ($f->isDir()) @rmdir($f->getRealPath());
    else @unlink($f->getRealPath());
  }
  @rmdir($dir);
  return is_dir($dir) ? 'Failed to delete repository' : true;
}

function gh_clone_repo($url) {
  $name = basename($url, '.git');
  $name = preg_replace('/[^a-zA-Z0-9_.-]/', '-', $name);
  $name = trim($name, '-.');
  $dir = GH_REPOS_DIR . '/' . $name;
  if (is_dir($dir)) return 'Repository already exists';
  $cmd = sprintf('git clone %s %s 2>&1', escapeshellarg($url), escapeshellarg($dir));
  $out = @shell_exec($cmd);
  return $out ? true : 'Failed to clone repository';
}

function gh_get_branches($name) {
  $dir = GH_REPOS_DIR . '/' . $name;
  if (!is_dir($dir . '/.git')) return [];
  $out = @shell_exec("git -C " . escapeshellarg($dir) . " branch -a 2>nul");
  if (!$out) return [];
  $branches = [];
  $lines = explode("\n", trim($out));
  foreach ($lines as $line) {
    $line = trim($line);
    if (!$line || strpos($line, 'HEAD') !== false) continue;
    $isCurrent = (strpos($line, '* ') === 0);
    $name = ltrim($line, '* ');
    $name = preg_replace('#^remotes/origin/#', '', $name);
    $name = trim($name);
    if (!$name || in_array($name, $branches)) continue;
    $branches[] = $name;
  }
  return array_unique($branches);
}

function gh_get_branch_info($name, $branch) {
  $dir = GH_REPOS_DIR . '/' . $name;
  $commitId = @shell_exec("git -C " . escapeshellarg($dir) . " rev-parse " . escapeshellarg($branch) . " 2>nul");
  if (!$commitId) return null;
  $commitId = trim($commitId);
  $msg = @shell_exec("git -C " . escapeshellarg($dir) . " log -1 --format=%s " . escapeshellarg($branch) . " 2>nul");
  $time = @shell_exec("git -C " . escapeshellarg($dir) . " log -1 --format=%cr " . escapeshellarg($branch) . " 2>nul");
  $author = @shell_exec("git -C " . escapeshellarg($dir) . " log -1 --format=\"%an <%ae>\" " . escapeshellarg($branch) . " 2>nul");
  return [
    'name' => $branch,
    'commit_id' => $commitId,
    'commit_id_short' => substr($commitId, 0, 8),
    'message' => $msg ? trim($msg) : '',
    'time' => $time ? trim($time) : '',
    'author' => $author ? trim($author) : '',
  ];
}

function gh_get_commits($name, $branch, $page = 1, $perPage = 30) {
  $dir = GH_REPOS_DIR . '/' . $name;
  if (!is_dir($dir . '/.git')) return [];
  $skip = ($page - 1) * $perPage;
  $out = @shell_exec("git -C " . escapeshellarg($dir) . " log " . escapeshellarg($branch) . " --skip=$skip -n $perPage --format=\"%H||%h||%an||%ae||%at||%s||%ar\" 2>nul");
  if (!$out) return [];
  $commits = [];
  foreach (explode("\n", trim($out)) as $line) {
    $parts = explode('||', $line, 7);
    if (count($parts) < 7) continue;
    $commits[] = [
      'hash' => $parts[0],
      'hash_short' => $parts[1],
      'author' => $parts[2],
      'author_email' => $parts[3],
      'time_unix' => $parts[4],
      'message' => $parts[5],
      'time_ago' => $parts[6],
    ];
  }
  return $commits;
}

function gh_get_commit_count($name, $branch) {
  $dir = GH_REPOS_DIR . '/' . $name;
  $count = @shell_exec("git -C " . escapeshellarg($dir) . " rev-list --count " . escapeshellarg($branch) . " 2>nul");
  return $count ? (int)trim($count) : 0;
}

function gh_get_commit($name, $sha) {
  $dir = GH_REPOS_DIR . '/' . $name;
  $out = @shell_exec("git -C " . escapeshellarg($dir) . " log -1 --format=\"%H||%h||%an||%ae||%cn||%ce||%at||%s||%b||%P||%ar\" " . escapeshellarg($sha) . " 2>nul");
  if (!$out) return null;
  $parts = explode('||', trim($out), 11);
  if (count($parts) < 11) return null;
  $commit = [
    'hash' => $parts[0],
    'hash_short' => $parts[1],
    'author' => $parts[2],
    'author_email' => $parts[3],
    'committer' => $parts[4],
    'committer_email' => $parts[5],
    'time_unix' => $parts[6],
    'message_title' => $parts[7],
    'message_body' => $parts[8],
    'parents' => array_filter(explode(' ', $parts[9])),
    'time_ago' => $parts[10],
  ];
  return $commit;
}

function gh_get_diff($name, $sha) {
  $dir = GH_REPOS_DIR . '/' . $name;
  $commit = gh_get_commit($name, $sha);
  if (!$commit) return null;
  $parent = $commit['parents'][0] ?? '';
  if ($parent) {
    $diff = @shell_exec("git -C " . escapeshellarg($dir) . " diff " . escapeshellarg($parent) . ".." . escapeshellarg($sha) . " 2>nul");
  } else {
    $diff = @shell_exec("git -C " . escapeshellarg($dir) . " show " . escapeshellarg($sha) . " --format=\"\" 2>nul");
  }
  return $diff ? gh_parse_diff($diff) : ['files' => [], 'stats' => ['files' => 0, 'additions' => 0, 'deletions' => 0]];
}

function gh_get_tags($name) {
  $dir = GH_REPOS_DIR . '/' . $name;
  if (!is_dir($dir . '/.git')) return [];
  $out = @shell_exec("git -C " . escapeshellarg($dir) . " tag -l 2>nul");
  if (!$out) return [];
  $tags = [];
  foreach (explode("\n", trim($out)) as $tag) {
    $tag = trim($tag);
    if (!$tag) continue;
    $info = @shell_exec("git -C " . escapeshellarg($dir) . " log -1 --format=\"%H||%h||%an||%ar||%s\" " . escapeshellarg($tag) . " 2>nul");
    $parts = $info ? explode('||', trim($info), 5) : [];
    $tags[] = [
      'name' => $tag,
      'commit_id' => $parts[0] ?? '',
      'commit_id_short' => $parts[1] ?? '',
      'author' => $parts[2] ?? '',
      'time_ago' => $parts[3] ?? '',
      'message' => $parts[4] ?? '',
    ];
  }
  return $tags;
}

function gh_search_code($name, $query, $branch = '') {
  $dir = GH_REPOS_DIR . '/' . $name;
  if (!is_dir($dir . '/.git') || !$query) return [];
  $ref = $branch ? escapeshellarg($branch) : 'HEAD';
  $out = @shell_exec("git -C " . escapeshellarg($dir) . " grep --null --line-number --fixed-strings -i " . escapeshellarg($query) . " " . $ref . " 2>nul");
  if (!$out) return [];
  $results = [];
  $blocks = explode("\0", $out);
  for ($i = 0; $i + 1 < count($blocks); $i += 2) {
    if (strpos($blocks[$i], ':') === false) continue;
    list($file, $lineno) = explode(':', $blocks[$i], 3);
    $content = $blocks[$i + 1] ?? '';
    $results[] = ['file' => $file, 'line' => (int)$lineno, 'content' => $content];
  }
  return $results;
}

function gh_short_sha($sha) {
  return substr($sha, 0, 8);
}

function gh_get_stars($name) {
  $f = GH_REPOS_DIR . '/' . $name . '/.git/gh-stars.json';
  return file_exists($f) ? (int)file_get_contents($f) : 0;
}

function gh_set_stars($name, $count) {
  @file_put_contents(GH_REPOS_DIR . '/' . $name . '/.git/gh-stars.json', (string)$count);
}

function gh_get_repo_visibility($name) {
  $f = GH_REPOS_DIR . '/' . $name . '/.git/gh-visibility.json';
  if (!file_exists($f)) return 'public';
  $v = trim(file_get_contents($f));
  return in_array($v, ['public', 'private']) ? $v : 'public';
}

function gh_set_repo_visibility($name, $visibility) {
  if (!in_array($visibility, ['public', 'private'])) return false;
  @file_put_contents(GH_REPOS_DIR . '/' . $name . '/.git/gh-visibility.json', $visibility);
  return true;
}

function gh_require_repo_access($name) {
  $v = gh_get_repo_visibility($name);
  if ($v === 'public') return true;
  if (is_user_logged_in()) return true;
  wp_die('This repository is private. <a href="' . wp_login_url() . '">Log in</a> to view.', '', ['response' => 403]);
}

function gh_get_recent_commits($limit = 10) {
  $repos = gh_get_repos();
  if (empty($repos)) return [];
  $all = [];
  foreach ($repos as $r) {
    $dir = GH_REPOS_DIR . '/' . $r['name'];
    $commits = gh_get_commits($r['name'], 'HEAD', 1, 5);
    foreach ($commits as $c) {
      $c['repo_name'] = $r['name'];
      $all[] = $c;
    }
  }
  usort($all, fn($a, $b) => ($b['time_unix'] ?? 0) - ($a['time_unix'] ?? 0));
  return array_slice($all, 0, $limit);
}

function gh_commit_file($dir, $path, $message) {
  $add = shell_exec("git -C " . escapeshellarg($dir) . " add " . escapeshellarg($path) . " 2>&1");
  $commit = shell_exec("git -C " . escapeshellarg($dir) . " commit -m " . escapeshellarg($message) . " 2>&1");
  return $commit;
}

function gh_get_diff_between($name, $base, $head) {
  $dir = GH_REPOS_DIR . '/' . $name;
  $diff = @shell_exec("git -C " . escapeshellarg($dir) . " diff " . escapeshellarg($base) . ".." . escapeshellarg($head) . " 2>nul");
  if (!$diff) return ['files' => [], 'stats' => ['files' => 0, 'additions' => 0, 'deletions' => 0]];
  return gh_parse_diff($diff);
}

function gh_get_compare_commits($name, $base, $head, $limit = 50) {
  $dir = GH_REPOS_DIR . '/' . $name;
  $out = @shell_exec("git -C " . escapeshellarg($dir) . " log " . escapeshellarg($base) . ".." . escapeshellarg($head) . " --oneline --format='%H||%h||%an||%ae||%at||%s||%ar' -n $limit 2>nul");
  if (!$out) return [];
  $commits = [];
  foreach (explode("\n", trim($out)) as $line) {
    $parts = explode('||', $line, 7);
    if (count($parts) < 7) continue;
    $commits[] = ['hash' => $parts[0], 'hash_short' => $parts[1], 'author' => $parts[2], 'author_email' => $parts[3], 'time_unix' => $parts[4], 'message' => $parts[5], 'time_ago' => $parts[6]];
  }
  return $commits;
}

function gh_parse_diff($diff) {
  $files = [];
  $currentFile = null;
  $additions = 0; $deletions = 0;
  foreach (explode("\n", $diff) as $line) {
    if (preg_match('/^diff --git a\/(.*) b\/(.*)/', $line, $m)) {
      if ($currentFile) $files[] = $currentFile;
      $currentFile = ['name' => $m[2], 'old_name' => $m[1], 'lines' => [], 'additions' => 0, 'deletions' => 0];
    } elseif ($currentFile && preg_match('/^@@ -(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))? @@(.*)/', $line, $m)) {
      $currentFile['lines'][] = ['type' => 'header', 'line' => $line, 'old_start' => $m[1], 'new_start' => $m[3], 'section' => trim($m[5] ?? '')];
    } elseif ($currentFile && preg_match('/^[+]/', $line) && !preg_match('/^[+]{3}/', $line)) {
      $currentFile['additions']++; $additions++;
      $currentFile['lines'][] = ['type' => 'add', 'line' => $line];
    } elseif ($currentFile && preg_match('/^[-]/', $line) && !preg_match('/^[-]{3}/', $line)) {
      $currentFile['deletions']++; $deletions++;
      $currentFile['lines'][] = ['type' => 'del', 'line' => $line];
    } elseif ($currentFile && preg_match('/^[ ]/', $line)) {
      $currentFile['lines'][] = ['type' => 'ctx', 'line' => $line];
    }
  }
  if ($currentFile) $files[] = $currentFile;
  return ['files' => $files, 'stats' => ['files' => count($files), 'additions' => $additions, 'deletions' => $deletions]];
}

function gh_repo_tabs($current, $name, $repo) {
  $tabs = [
    'code' => ['label' => 'Code', 'url' => home_url('/repo/' . urlencode($name))],
    'issues' => ['label' => 'Issues', 'url' => home_url('/repo/' . urlencode($name) . '/issues/')],
    'commits' => ['label' => 'Commits', 'url' => home_url('/repo/' . urlencode($name) . '/commits/' . urlencode($repo['branch']))],
    'branches' => ['label' => 'Branches', 'url' => home_url('/repo/' . urlencode($name) . '/branches/')],
    'tags' => ['label' => 'Tags', 'url' => home_url('/repo/' . urlencode($name) . '/tags/')],
    'settings' => ['label' => 'Settings', 'url' => home_url('/repo/' . urlencode($name) . '/settings/')],
  ];
  $httpUrl = home_url('/git/' . urlencode($name));
  $sshUrl = 'git@git.local:' . urlencode($name) . '.git';
  ?>
  <div class="gh-repo-nav">
    <div class="gh-repo-nav-inner">
      <?php foreach ($tabs as $key => $tab): ?>
        <a href="<?= $tab['url'] ?>" class="<?= $current === $key ? 'active' : '' ?>"><?= $tab['label'] ?></a>
      <?php endforeach; ?>
      <div style="margin-left:auto;display:flex;align-items:center;padding:8px 0;">
        <div class="gh-clone-dropdown" style="position:relative;">
          <button onclick="toggleCloneMenu(this)" style="display:flex;align-items:center;gap:6px;background:#238636;padding:5px 14px;border:1px solid rgba(240,246,252,0.1);border-radius:6px;color:#fff;font-size:13px;font-weight:500;cursor:pointer;white-space:nowrap;">
            <svg height="16" viewBox="0 0 16 16" width="16" fill="#fff"><path d="M5.75 1a.75.75 0 01.75.75v3c0 .414.336.75.75.75h2a.75.75 0 010 1.5h-2A2.25 2.25 0 015 4.75v-3A.75.75 0 015.75 1zm3.5 5a.75.75 0 01.75-.75h1.25a.75.75 0 010 1.5H10a.75.75 0 01-.75-.75z"/><path d="M10.5 1H12a1 1 0 011 1v1.5a1 1 0 01-1 1h-1.5a1 1 0 01-1-1V2a1 1 0 011-1z"/></svg>
            Code
            <svg height="16" viewBox="0 0 16 16" width="16" fill="#fff"><path d="M4.427 6.427l3.396 3.396a.25.25 0 00.354 0l3.396-3.396A.25.25 0 0011.396 6H4.604a.25.25 0 00-.177.427z"/></svg>
          </button>
          <div class="gh-clone-menu" style="display:none;position:absolute;top:100%;right:0;z-index:100;background:#161b22;border:1px solid #30363d;border-radius:8px;padding:16px;width:320px;margin-top:4px;box-shadow:0 8px 24px rgba(0,0,0,0.4);">
            <div style="font-size:14px;font-weight:600;color:#e6edf3;margin-bottom:8px;">Clone</div>
            <div style="display:flex;margin-bottom:8px;background:#0d1117;border:1px solid #30363d;border-radius:6px;padding:3px;">
              <button onclick="switchCloneProtocol('http')" id="gh-clone-http-btn" style="flex:1;padding:5px 12px;border:none;border-radius:4px;font-size:12px;cursor:pointer;background:#1f6feb;color:#fff;font-weight:500;">HTTPS</button>
              <button onclick="switchCloneProtocol('ssh')" id="gh-clone-ssh-btn" style="flex:1;padding:5px 12px;border:none;border-radius:4px;font-size:12px;cursor:pointer;background:transparent;color:#8b949e;">SSH</button>
            </div>
            <div style="display:flex;align-items:center;gap:4px;margin-bottom:12px;">
              <input type="text" id="gh-clone-url" value="<?= esc_attr($httpUrl) ?>" readonly style="flex:1;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#e6edf3;padding:8px 12px;font-size:12px;font-family:monospace;">
            </div>
            <div style="margin-bottom:12px;">
              <a href="github-windows://openRepo/<?= urlencode($httpUrl) ?>" style="display:flex;align-items:center;gap:6px;padding:6px 0;font-size:13px;color:#8b949e;text-decoration:none;">
                <svg height="16" viewBox="0 0 16 16" width="16" fill="#8b949e"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
                Open with GitHub Desktop
              </a>
            </div>
            <div style="border-top:1px solid #21262d;padding-top:12px;">
              <a href="<?= home_url('/repo/' . urlencode($name) . '/archive/' . urlencode($repo['branch']) . '.zip') ?>" style="display:flex;align-items:center;gap:6px;padding:6px 0;font-size:13px;color:#8b949e;text-decoration:none;">
                <svg height="16" viewBox="0 0 16 16" width="16" fill="#8b949e"><path d="M3.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5zM6 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5A.5.5 0 016 13zM8.5 13a.5.5 0 01-.5-.5V9a.5.5 0 011 0v3.5a.5.5 0 01-.5.5z"/></svg>
                Download ZIP
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
  function toggleCloneMenu(btn) {
    var menu = btn.parentNode.querySelector('.gh-clone-menu');
    var isHidden = menu.style.display === 'none';
    document.querySelectorAll('.gh-clone-menu').forEach(function(m) { m.style.display = 'none'; });
    if (isHidden) menu.style.display = 'block';
  }
  function switchCloneProtocol(proto) {
    var httpBtn = document.getElementById('gh-clone-http-btn');
    var sshBtn = document.getElementById('gh-clone-ssh-btn');
    var input = document.getElementById('gh-clone-url');
    if (proto === 'http') {
      httpBtn.style.background = '#1f6feb'; httpBtn.style.color = '#fff';
      sshBtn.style.background = 'transparent'; sshBtn.style.color = '#8b949e';
      input.value = '<?= esc_js($httpUrl) ?>';
    } else {
      sshBtn.style.background = '#1f6feb'; sshBtn.style.color = '#fff';
      httpBtn.style.background = 'transparent'; httpBtn.style.color = '#8b949e';
      input.value = '<?= esc_js($sshUrl) ?>';
    }
  }
  document.addEventListener('click', function(e) {
    var dd = document.querySelector('.gh-clone-dropdown');
    if (dd && !dd.contains(e.target)) {
      var m = dd.querySelector('.gh-clone-menu');
      if (m) m.style.display = 'none';
    }
  });
  </script>
  <?php
}

function gh_repo_header($name, $repo) {
  $stars = gh_get_stars($name);
  ?>
  <div class="gh-repo-header">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <h1><?= esc_html($name) ?></h1>
        <div class="gh-meta">
          <?php if ($repo['branch']): ?><span>Branch: <strong><?= esc_html($repo['branch']) ?></strong></span><?php endif; ?>
          <span>Updated <?= esc_html($repo['updated']) ?></span>
          <?php if ($repo['size']): ?><span>Repo size: <?= esc_html($repo['size']) ?></span><?php endif; ?>
        </div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <a href="<?= home_url('/repo/' . urlencode($name) . '/star?op=' . ($stars > 0 ? 'remove' : 'add')) ?>" class="gh-btn" style="font-size:13px;">
          <span style="color:<?= $stars > 0 ? '#e3b341' : '#8b949e' ?>;">&#9733;</span>
          Star
          <span style="color:#8b949e;margin-left:4px;"><?= $stars ?></span>
        </a>
        <a href="<?= home_url('/repo/' . urlencode($name) . '/new-file/' . urlencode($repo['branch'])) ?>" class="gh-btn" style="font-size:13px;">+ New file</a>
        <a href="<?= home_url('/repo/' . urlencode($name) . '/upload/' . urlencode($repo['branch']) . '/' ) ?>" class="gh-btn" style="font-size:13px;">Upload</a>
        <a href="<?= home_url('/repo/' . urlencode($name) . '/fork/') ?>" class="gh-btn" style="font-size:13px;">Fork</a>
      </div>
    </div>
  </div>
  <?php
}

function gh_pagination($current, $total, $urlPattern) {
  if ($total <= 1) return;
  $pages = min($total, 20);
  ?>
  <div style="display:flex;justify-content:center;gap:8px;margin-top:24px;">
    <?php if ($current > 1): ?>
      <a href="<?= sprintf($urlPattern, $current - 1) ?>" class="gh-btn">Previous</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a href="<?= sprintf($urlPattern, $i) ?>" class="gh-btn" style="<?= $i === $current ? 'background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($current < $total): ?>
      <a href="<?= sprintf($urlPattern, $current + 1) ?>" class="gh-btn">Next</a>
    <?php endif; ?>
  </div>
  <?php
}

function gh_get_contribution_data($days = 365) {
  $repos = gh_get_repos();
  $daily = [];
  $now = time();
  for ($i = $days; $i >= 0; $i--) {
    $date = date('Y-m-d', $now - $i * 86400);
    $daily[$date] = 0;
  }
  foreach ($repos as $r) {
    $dir = GH_REPOS_DIR . '/' . $r['name'];
    $out = @shell_exec("git -C " . escapeshellarg($dir) . " log --since=\"" . $days . ".days.ago\" --format=\"%ai\" 2>nul");
    if (!$out) continue;
    foreach (explode("\n", trim($out)) as $line) {
      $d = substr(trim($line), 0, 10);
      if ($d && isset($daily[$d])) $daily[$d]++;
    }
  }
  return $daily;
}

function gh_get_pinned_repos($count = 6) {
  $repos = gh_get_repos();
  usort($repos, fn($a, $b) => ($b['stars'] ?? 0) - ($a['stars'] ?? 0));
  return array_slice($repos, 0, $count);
}

// Issue tracker
function gh_register_issue_post_type() {
  register_post_type('gh_issue', [
    'labels' => ['name' => 'Issues', 'singular_name' => 'Issue'],
    'public' => false,
    'show_ui' => false,
    'supports' => ['title', 'editor', 'author', 'comments', 'custom-fields'],
    'rewrite' => false,
    'query_var' => false,
  ]);
  register_taxonomy('gh_label', 'gh_issue', [
    'labels' => ['name' => 'Labels', 'singular_name' => 'Label'],
    'public' => false,
    'rewrite' => false,
    'hierarchical' => false,
  ]);
}
add_action('init', 'gh_register_issue_post_type');

function gh_create_issue($repo, $title, $body, $labels = []) {
  $id = wp_insert_post([
    'post_type' => 'gh_issue',
    'post_title' => $title,
    'post_content' => $body,
    'post_status' => 'publish',
    'post_author' => get_current_user_id(),
    'meta_input' => ['gh_repo' => $repo, 'gh_status' => 'open'],
  ]);
  if (is_wp_error($id)) return $id;
  if ($labels) wp_set_object_terms($id, $labels, 'gh_label');
  return $id;
}

function gh_get_issues($repo, $status = 'open', $label = '') {
  $args = [
    'post_type' => 'gh_issue',
    'posts_per_page' => -1,
    'meta_key' => 'gh_repo',
    'meta_value' => $repo,
    'meta_query' => [['key' => 'gh_status', 'value' => $status]],
  ];
  if ($label) $args['tax_query'] = [['taxonomy' => 'gh_label', 'field' => 'slug', 'terms' => $label]];
  return get_posts($args);
}

function gh_get_issue($id) {
  $post = get_post($id);
  if (!$post || $post->post_type !== 'gh_issue') return null;
  return $post;
}

function gh_get_issue_comments($id) {
  return get_comments(['post_id' => $id, 'order' => 'ASC']);
}

function gh_close_issue($id) {
  update_post_meta($id, 'gh_status', 'closed');
}

function gh_reopen_issue($id) {
  update_post_meta($id, 'gh_status', 'open');
}
