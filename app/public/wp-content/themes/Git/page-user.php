<?php get_header();
$user = wp_get_current_user();
if (!$user->exists()) {
  $users = get_users(['role' => 'administrator', 'number' => 1]);
  $user = $users[0] ?? $user;
}
$username = $user->display_name ?: $user->user_login;
$userhandle = $user->user_login;
$repos = gh_get_repos();
$total_repos = count($repos);
$total_stars = array_sum(array_column($repos, 'stars'));
$contributions = gh_get_contribution_data(365);
$total_contributions = array_sum($contributions);
$pinned = gh_get_pinned_repos(6);
$activity = gh_get_recent_commits(20);
$max_daily = max($contributions) ?: 1;
$today = date('Y-m-d');
$weekday_names = ['', 'Mon', '', 'Wed', '', 'Fri', ''];
$month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
?>
<style>
.gh-contribution-graph { border:1px solid #30363d; border-radius:6px; overflow:hidden; margin-bottom:24px; }
.gh-contribution-header { background:#161b22; padding:16px 16px 0; }
.gh-contribution-body { background:#0d1117; padding:16px 16px 0; overflow-x:auto; }
.gh-contribution-footer { background:#0d1117; padding:4px 16px 16px; display:flex; justify-content:space-between; align-items:center; font-size:11px; color:#8b949e; }
.gh-contribution-cell { width:13px; height:13px; border-radius:2px; }
.gh-contribution-cell:hover { outline:2px solid rgba(255,255,255,0.3); }
.gh-pinned-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px; }
.gh-pinned-card { border:1px solid #30363d; border-radius:6px; padding:16px; background:#0d1117; }
.gh-pinned-card:hover { border-color:#8b949e; }
.gh-pinned-card h4 { font-size:14px; font-weight:600; margin-bottom:4px; }
.gh-pinned-card p { font-size:12px; color:#8b949e; margin-bottom:8px; }
.gh-tab-bar { border-bottom:1px solid #21262d; margin-bottom:24px; display:flex; gap:0; }
.gh-tab-bar a { padding:8px 16px; font-size:14px; color:#8b949e; border-bottom:2px solid transparent; margin-bottom:-1px; }
.gh-tab-bar a.active { color:#c9d1d9; border-bottom-color:#f78166; }
.gh-tab-bar a:hover { color:#c9d1d9; text-decoration:none; }
.gh-profile-avatar { width:260px; height:260px; border-radius:50%; background:#161b22; border:1px solid #30363d; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:16px; }
.gh-profile-avatar svg { fill:#8b949e; }
.gh-timeline-item { display:flex; gap:12px; padding:8px 0; border-left:2px solid #21262d; margin-left:8px; padding-left:20px; position:relative; }
.gh-timeline-item::before { content:''; position:absolute; left:-5px; top:14px; width:8px; height:8px; border-radius:50%; background:#21262d; border:2px solid #0d1117; }
.gh-timeline-dot { position:absolute; left:-5px; top:14px; width:8px; height:8px; border-radius:50%; background:#238636; border:2px solid #0d1117; }
.gh-timeline-item:first-child { margin-top:0; }
.gh-timeline-item:last-child { border-left-color:transparent; }
</style>
<div class="gh-content">
  <div class="gh-container">
    <div style="display:flex;gap:40px;">
      <div style="width:296px;min-width:296px;">
        <div class="gh-profile-avatar">
          <svg height="120" viewBox="0 0 16 16" width="120"><path d="M8 8a3 3 0 100-6 3 3 0 000 6zm2-3a2 2 0 11-4 0 2 2 0 014 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
        </div>
        <h1 style="font-size:24px;font-weight:600;color:#c9d1d9;line-height:1.25;"><?= esc_html($username) ?></h1>
        <div style="font-size:18px;color:#8b949e;margin-bottom:12px;"><?= esc_html($userhandle) ?></div>
        <div style="font-size:14px;color:#c9d1d9;margin-bottom:16px;line-height:1.5;"><?= esc_html($user->description ?: 'No bio yet') ?></div>
        <div style="margin-bottom:16px;">
          <a href="<?= home_url('/user') ?>" class="gh-btn" style="width:100%;justify-content:center;padding:6px 0;font-size:13px;">Edit profile</a>
        </div>
        <div style="font-size:13px;color:#8b949e;line-height:1.8;">
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
            <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;flex-shrink:0;"><path d="M8 4a4 4 0 100 8 4 4 0 000-8z"/></svg>
            <span>Not available</span>
          </div>
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
            <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;flex-shrink:0;"><path d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"/></svg>
            <a href="<?= home_url() ?>" style="color:#58a6ff;">gitfren.local</a>
          </div>
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
            <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;flex-shrink:0;"><path d="M1.75 2h12.5a.25.25 0 01.25.25v11.5a.25.25 0 01-.25.25H1.75a.25.25 0 01-.25-.25V2.25A.25.25 0 011.75 2zM0 2.25C0 1.56.56 1 1.25 1h13.5c.69 0 1.25.56 1.25 1.25v11.5c0 .69-.56 1.25-1.25 1.25H1.25C.56 15 0 14.44 0 13.75V2.25z"/></svg>
            <span>admin@gitfren.local</span>
          </div>
          <div style="display:flex;align-items:center;gap:6px;">
            <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;flex-shrink:0;"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zM4.5 7.5a.5.5 0 000 1h7a.5.5 0 000-1h-7z"/></svg>
            <span>Joined <?= date('F Y', strtotime($user->user_registered)) ?></span>
          </div>
        </div>
        <div style="margin-top:16px;font-size:14px;">
          <a href="#" style="color:#c9d1d9;font-weight:500;"><strong>0</strong> <span style="color:#8b949e;font-weight:400;">followers</span></a>
          <span style="margin:0 4px;color:#8b949e;">·</span>
          <a href="#" style="color:#c9d1d9;font-weight:500;"><strong>0</strong> <span style="color:#8b949e;font-weight:400;">following</span></a>
        </div>
        <div style="margin-top:16px;border-top:1px solid #21262d;padding-top:16px;font-size:13px;color:#8b949e;">
          <strong style="color:#c9d1d9;">Highlights</strong>
          <div style="margin-top:8px;display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="font-size:16px;">&#9733;</span>
              <span><strong style="color:#c9d1d9;"><?= $total_stars ?></strong> stars earned</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;flex-shrink:0;"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 010-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1V9h-8c-.356 0-.694.074-1 .208V2.5a1 1 0 011-1h8zM5 12.25v3.25a.25.25 0 00.4.2l1.45-1.087a.25.25 0 01.3 0L8.6 15.7a.25.25 0 00.4-.2v-3.25a.25.25 0 00-.25-.25h-3.5a.25.25 0 00-.25.25z"/></svg>
              <span><strong style="color:#c9d1d9;"><?= $total_repos ?></strong> repositories</span>
            </div>
          </div>
        </div>
      </div>
      <div style="flex:1;min-width:0;">
        <div class="gh-tab-bar">
          <a href="#" class="active">Overview</a>
          <a href="<?= home_url() ?>">Repositories <span style="color:#8b949e;font-size:12px;"><?= $total_repos ?></span></a>
          <a href="#">Stars</a>
        </div>

        <div class="gh-contribution-graph">
          <div class="gh-contribution-header">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:8px;"><?= number_format($total_contributions) ?> contributions in the last year</h3>
          </div>
          <div class="gh-contribution-body">
            <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
              <tr>
                <td style="width:32px;font-size:10px;color:#8b949e;vertical-align:bottom;padding:0 4px 4px 0;"></td>
                <?php
                $weeks = [];
                $day_index = 0;
                foreach ($contributions as $date => $count) {
                  $dow = (int)date('w', strtotime($date));
                  $week_index = (int)(($day_index + $dow) / 7);
                  if (!isset($weeks[$week_index])) $weeks[$week_index] = [];
                  $weeks[$week_index][$dow] = $count;
                  $day_index++;
                }
                $months_shown = [];
                foreach ($weeks as $wi => $days) {
                  $first_day = min(array_keys($days));
                  $timestamp = strtotime(key($days));
                  $month_num = (int)date('n', $timestamp);
                  if ($first_day === 0 && !in_array($month_num, $months_shown)) {
                    $months_shown[] = $month_num;
                    echo '<td style="padding:0 2px 4px;font-size:10px;color:#8b949e;text-align:center;">' . $month_names[$month_num - 1] . '</td>';
                  } elseif ($first_day === 0) {
                    echo '<td style="padding:0 2px 4px;"></td>';
                  }
                }
                ?>
              </tr>
              <?php for ($dow = 0; $dow < 7; $dow++): ?>
              <tr>
                <td style="font-size:10px;color:#8b949e;padding:0 4px 2px 0;text-align:right;width:32px;"><?= $weekday_names[$dow] ?? '' ?></td>
                <?php foreach ($weeks as $wi => $days):
                  $count = $days[$dow] ?? 0;
                  $intensity = $count > 0 ? ceil(($count / $max_daily) * 4) : 0;
                  $colors = ['#0d1117', '#0e4429', '#006d32', '#26a641', '#39d353'];
                  $fill = $colors[$intensity] ?? '#0d1117';
                  $title = $count . ' contributions';
                ?>
                <td style="padding:1px;"><div class="gh-contribution-cell" style="background:<?= $fill ?>;" title="<?= $title ?>"></div></td>
                <?php endforeach; ?>
              </tr>
              <?php endfor; ?>
            </table>
          </div>
          <div class="gh-contribution-footer">
            <span>Learn how we count contributions</span>
            <div style="display:flex;align-items:center;gap:4px;">
              <span>Less</span>
              <div style="width:13px;height:13px;border-radius:2px;background:#0d1117;border:1px solid #1b1f23;"></div>
              <div style="width:13px;height:13px;border-radius:2px;background:#0e4429;"></div>
              <div style="width:13px;height:13px;border-radius:2px;background:#006d32;"></div>
              <div style="width:13px;height:13px;border-radius:2px;background:#26a641;"></div>
              <div style="width:13px;height:13px;border-radius:2px;background:#39d353;"></div>
              <span>More</span>
            </div>
          </div>
        </div>

        <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;">Popular repositories</h3>
        <div class="gh-pinned-grid">
          <?php if (empty($pinned)): ?>
          <div style="grid-column:1/-1;padding:24px;color:#8b949e;text-align:center;border:1px solid #30363d;border-radius:6px;">
            No repositories yet. <a href="<?= home_url('/new-repo') ?>" style="color:#58a6ff;">Create one</a>.
          </div>
          <?php else: foreach ($pinned as $repo):
            $name = $repo['name'];
            $desc = $repo['desc'];
            $lang = $repo['lang'];
            $stars = $repo['stars'];
            $updated = $repo['updated'];
          ?>
          <div class="gh-pinned-card">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
              <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;flex-shrink:0;"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 010-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1V9h-8c-.356 0-.694.074-1 .208V2.5a1 1 0 011-1h8zM5 12.25v3.25a.25.25 0 00.4.2l1.45-1.087a.25.25 0 01.3 0L8.6 15.7a.25.25 0 00.4-.2v-3.25a.25.25 0 00-.25-.25h-3.5a.25.25 0 00-.25.25z"/></svg>
              <h4><a href="<?= home_url('/repo/' . urlencode($name)) ?>" style="color:#58a6ff;"><?= esc_html($name) ?></a></h4>
            </div>
            <p><?= esc_html($desc ?: 'No description') ?></p>
            <div style="display:flex;align-items:center;gap:12px;font-size:12px;color:#8b949e;">
              <?php if ($lang): ?>
              <span><span class="gh-lang-dot" style="background:<?= gh_lang_color($lang) ?>"></span><?= esc_html($lang) ?></span>
              <?php endif; ?>
              <span><span style="font-size:14px;">&#9733;</span> <?= $stars ?></span>
              <span style="margin-left:auto;">Updated <?= esc_html($updated) ?></span>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;">Contribution activity</h3>
        <?php
        $grouped = [];
        foreach ($activity as $c) {
          $date = date('F j, Y', $c['time_unix']);
          $grouped[$date][] = $c;
        }
        if (empty($grouped)): ?>
        <div style="padding:24px;color:#8b949e;text-align:center;border:1px solid #30363d;border-radius:6px;">
          No contributions yet
        </div>
        <?php else: foreach ($grouped as $date_label => $items): ?>
        <div style="margin-bottom:24px;">
          <h4 style="font-size:14px;color:#c9d1d9;margin-bottom:8px;font-weight:600;"><?= esc_html($date_label) ?></h4>
          <?php foreach ($items as $c): ?>
          <div class="gh-timeline-item">
            <span style="position:absolute;left:-5px;top:14px;width:8px;height:8px;border-radius:50%;background:#238636;border:2px solid #0d1117;"></span>
            <div style="flex:1;min-width:0;">
              <div style="display:flex;gap:8px;align-items:flex-start;">
                <svg height="16" viewBox="0 0 16 16" width="16" style="fill:#8b949e;flex-shrink:0;margin-top:2px;"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zM4.5 7.5a.5.5 0 000 1h7a.5.5 0 000-1h-7z"/></svg>
                <div style="flex:1;min-width:0;">
                  <div style="font-size:13px;color:#c9d1d9;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <a href="<?= home_url('/repo/' . urlencode($c['repo_name']) . '/commit/' . $c['hash']) ?>" style="color:#c9d1d9;font-weight:600;"><?= esc_html($c['message']) ?></a>
                  </div>
                  <div style="font-size:12px;color:#8b949e;margin-top:2px;">
                    <a href="<?= home_url('/repo/' . urlencode($c['repo_name'])) ?>" style="color:#58a6ff;"><?= esc_html($c['repo_name']) ?></a>
                    <span style="margin:0 4px;">·</span>
                    <?= esc_html($c['time_ago']) ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>
<?php get_footer(); ?>