<?php
$name = get_query_var('gh_repo');
$repo = gh_get_repo($name);
if (!$repo) { wp_die('Repository not found.', '', ['response' => 404]); }
$id = (int)get_query_var('gh_issue_id');
$issue = gh_get_issue($id);
if (!$issue || get_post_meta($id, 'gh_repo', true) !== $name) { wp_die('Issue not found.', '', ['response' => 404]); }
$status = get_post_meta($id, 'gh_status', true);
$labels = wp_get_object_terms($id, 'gh_label');
$comments = gh_get_issue_comments($id);
$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
  if (!is_user_logged_in()) { $commentError = 'Login required.'; }
  else {
    $c = trim($_POST['comment']);
    if ($c) {
      wp_insert_comment([
        'comment_post_ID' => $id,
        'comment_content' => $c,
        'user_id' => get_current_user_id(),
        'comment_author' => wp_get_current_user()->display_name,
      ]);
      wp_redirect(home_url('/repo/' . urlencode($name) . '/issues/' . $id));
      exit;
    }
  }
}
get_header(); ?>
<?php gh_repo_tabs('code', $name, $repo); ?>
<?php gh_repo_header($name, $repo); ?>
<div class="gh-container" style="max-width:800px;">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
    <h2 style="font-size:18px;"><?= esc_html($issue->post_title) ?></h2>
    <span style="padding:4px 12px;border-radius:12px;font-size:12px;font-weight:500;<?= $status === 'open' ? 'background:#238636;color:#fff;' : 'background:#8b949e;color:#fff;' ?>"><?= $status === 'open' ? 'Open' : 'Closed' ?></span>
  </div>
  <div style="background:#161b22;border:1px solid #30363d;border-radius:6px;margin-bottom:16px;">
    <div style="padding:12px 16px;border-bottom:1px solid #30363d;font-size:12px;color:#8b949e;">
      <strong><?= esc_html(get_the_author_meta('display_name', $issue->post_author)) ?></strong> opened <?= esc_html(human_time_diff(strtotime($issue->post_date))) ?> ago
      <?php foreach ($labels as $l): ?>
      <span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;background:#1f6feb;color:#fff;margin-left:4px;"><?= esc_html($l->name) ?></span>
      <?php endforeach; ?>
    </div>
    <div style="padding:16px;font-size:14px;color:#e6edf3;line-height:1.6;white-space:pre-wrap;"><?= esc_html($issue->post_content) ?></div>
  </div>
  <?php if ($comments): ?>
  <h3 style="font-size:14px;margin-bottom:12px;color:#8b949e;">Comments</h3>
  <?php foreach ($comments as $cm): ?>
  <div style="background:#161b22;border:1px solid #30363d;border-radius:6px;margin-bottom:12px;">
    <div style="padding:8px 16px;border-bottom:1px solid #30363d;font-size:12px;color:#8b949e;">
      <strong><?= esc_html($cm->comment_author) ?></strong> commented <?= esc_html(human_time_diff(strtotime($cm->comment_date))) ?> ago
    </div>
    <div style="padding:16px;font-size:14px;color:#e6edf3;white-space:pre-wrap;"><?= esc_html($cm->comment_content) ?></div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
  <?php if (is_user_logged_in()): ?>
  <form method="post" style="margin-top:16px;">
    <?php if ($commentError): ?>
    <div style="background:#2d1517;border:1px solid #f85149;border-radius:6px;padding:12px 16px;margin-bottom:12px;color:#f85149;"><?= esc_html($commentError) ?></div>
    <?php endif; ?>
    <textarea name="comment" placeholder="Leave a comment" rows="4" style="width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#e6edf3;padding:10px 12px;font-size:14px;font-family:monospace;margin-bottom:8px;"></textarea>
    <div style="display:flex;gap:8px;">
      <button type="submit" class="gh-btn" style="background:#238636;border-color:rgba(240,246,252,0.1);color:#fff;padding:8px 16px;font-size:13px;">Comment</button>
      <?php if ($status === 'open'): ?>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/issues/' . $id . '/close') ?>" class="gh-btn" style="font-size:13px;padding:8px 16px;color:#f85149;text-decoration:none;">Close issue</a>
      <?php else: ?>
      <a href="<?= home_url('/repo/' . urlencode($name) . '/issues/' . $id . '/reopen') ?>" class="gh-btn" style="font-size:13px;padding:8px 16px;color:#3fb950;text-decoration:none;">Reopen issue</a>
      <?php endif; ?>
    </div>
  </form>
  <?php endif; ?>
</div>
<?php get_footer(); ?>