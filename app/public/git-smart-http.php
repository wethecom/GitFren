<?php
/**
 * Git Smart HTTP Protocol handler for PHP
 * Handles git-upload-pack (clone/fetch) and git-receive-pack (push) via HTTP
 */

// Ensure git is available on PATH
$gitCmdPath = defined('GIT_CMD_PATH') ? GIT_CMD_PATH : 'C:\\Program Files\\Git\\cmd';

// Ensure git is available on PATH
$gitCmdPath = defined('GIT_CMD_PATH') ? GIT_CMD_PATH : 'C:\\Program Files\\Git\\cmd';
$envPath = getenv('PATH');
if (strpos($envPath, $gitCmdPath) === false) {
  putenv('PATH=' . $gitCmdPath . ';' . $envPath);
}

$route = $_GET['git_route'] ?? '';
if (empty($route)) {
  header('HTTP/1.1 400 Bad Request');
  exit('Bad request');
}

$parts = explode('/', $route, 2);
$repoName = $parts[0] ?? '';
$service = $parts[1] ?? '';

if (empty($repoName)) {
  header('HTTP/1.1 404 Not Found');
  exit('Repository not specified');
}

$reposBase = dirname(__DIR__, 2) . '/repos';
$repoDir = $reposBase . '/' . $repoName;
if (!is_dir($repoDir) && !is_dir($repoDir . '.git')) {
  header('HTTP/1.1 404 Not Found');
  exit('Repository not found');
}

// Determine git dir
if (is_dir($repoDir) && is_file($repoDir . '/HEAD') && !is_dir($repoDir . '/.git')) {
  $gitDir = $repoDir;
} elseif (is_dir($repoDir . '/.git')) {
  $gitDir = $repoDir . '/.git';
} else {
  $gitDir = $repoDir;
}

function run_git($cmd, $input = null) {
  // Use temp files to avoid Windows pipe buffer deadlocks
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

$queryService = $_GET['service'] ?? '';

// Handle info/refs (advertisement)
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
  list($ok, $out, $err) = run_git($cmd);
  if (!$ok) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Git error: ' . $err);
  }
  header('Content-Type: ' . $contentType);
  header('Expires: Fri, 01 Jan 1980 00:00:00 GMT');
  header('Pragma: no-cache');
  header('Cache-Control: no-cache, max-age=0, must-revalidate');

  // Prepend the service header (git-http-backend normally adds this)
  $serviceHeader = ($queryService === 'git-upload-pack')
    ? "# service=git-upload-pack\n"
    : "# service=git-receive-pack\n";
  $pktLen = strlen($serviceHeader) + 4;
  $pktLine = sprintf('%04x', $pktLen) . $serviceHeader;
  echo $pktLine . '0000' . $out;
  exit;
}

// Handle upload-pack (fetch/clone)
if ($service === 'git-upload-pack') {
  $input = file_get_contents('php://input');
  // If no input, try stdin
  if (empty($input)) $input = '0000';
  $cmd = 'git upload-pack --stateless-rpc ' . escapeshellarg($gitDir);
  list($ok, $out, $err) = run_git($cmd, $input);
  if (!$ok) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Git error: ' . $err);
  }
  header('Content-Type: application/x-git-upload-pack-result');
  echo $out;
  exit;
}

// Handle receive-pack (push)
if ($service === 'git-receive-pack') {
  $input = file_get_contents('php://input');
  $cmd = 'git receive-pack --stateless-rpc ' . escapeshellarg($gitDir);
  list($ok, $out, $err) = run_git($cmd, $input);
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
