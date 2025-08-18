<?php
/**
 * Dashboard View – AI Blog Generator PRO
 * Path: admin/views/dashboard.php
 * Safe to include directly via the Admin loader.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Options & data
$opts = get_option('ai_bgpro_options', [
  'industries'       => ['Fitness','Beauty','Technology','Travel','Finance'],
  'articles_per_day' => 3,
  'run_times'        => '09:00,14:00,20:00',
  'auto_publish'     => 'enabled',
  'theme'            => 'dark',
]);

$queue   = get_option('ai_bgpro_queue', []);
$inds    = $opts['industries'];
$inds    = is_array($inds) ? $inds : array_filter(array_map('trim', explode(',', (string)$inds)));

$articles_today = 0;
try {
  $q_today = new WP_Query([
    'post_type'      => 'post',
    'date_query'     => [ [ 'after' => 'today' ] ],
    'posts_per_page' => -1,
    'fields'         => 'ids',
    'post_status'    => ['publish','draft','pending'],
  ]);
  $articles_today = intval($q_today->found_posts);
} catch (Throwable $e) { $articles_today = 0; }

// Recent posts (latest 5)
$recent_posts = get_posts([
  'numberposts' => 5,
  'post_type'   => 'post',
  'post_status' => ['publish','draft','pending'],
  'orderby'     => 'date',
  'order'       => 'DESC',
]);

// Cron status (basic signal)
$cron_hook      = 'ai_bgpro_cron_run';
$cron_scheduled = (bool) wp_next_scheduled( $cron_hook );

// Helpers
function ai_bgpro_badge($text, $type='info'){
  $colors = [
    'ok'    => 'style="color:#57c490"',
    'warn'  => 'style="color:#f4c363"',
    'err'   => 'style="color:#c25555"',
    'info'  => 'style="color:#a5accb"',
  ];
  $c = $colors[$type] ?? $colors['info'];
  return '<span class="badge" '.$c.'>'.esc_html($text).'</span>';
}
?>

<div class="grid" style="grid-template-columns: repeat(12, 1fr); gap:16px;">

  <!-- Welcome / Header -->
  <div class="card" style="grid-column: span 12;">
    <h2 style="margin-bottom:6px;">AI Blog Generator PRO — Dashboard</h2>
    <p class="hint">Privire de ansamblu: KPI, coadă, programare, articole recente și scurtături.</p>
    <div class="mt-1">
      <?php
        echo ai_bgpro_badge( 'Modern 2025', 'info' ).' ';
        echo ai_bgpro_badge( ($opts['auto_publish'] ?? '') === 'enabled' ? 'Auto publish: ON' : 'Auto publish: OFF', ($opts['auto_publish'] ?? '')==='enabled' ? 'ok' : 'warn' ).' ';
        echo ai_bgpro_badge( $cron_scheduled ? 'WP-Cron: scheduled' : 'WP-Cron: not scheduled', $cron_scheduled ? 'ok' : 'warn' );
      ?>
    </div>
  </div>

  <!-- KPI Row -->
  <div class="kpi card" style="grid-column: span 4;">
    <div class="kpi-label">Articles today</div>
    <div class="kpi-value"><strong style="font-size:22px;"><?php echo esc_html( number_format_i18n($articles_today) ); ?></strong></div>
    <p class="hint">Total articole create (publicate/draft) în ultimele 24h.</p>
  </div>

  <div class="kpi card" style="grid-column: span 4;">
    <div class="kpi-label">Queued keywords</div>
    <div class="kpi-value"><strong style="font-size:22px;"><?php echo esc_html( number_format_i18n( count($queue) ) ); ?></strong></div>
    <p class="hint">Câte keyword-uri sunt în coadă pentru Auto-Schedule.</p>
  </div>

  <div class="kpi card" style="grid-column: span 4;">
    <div class="kpi-label">Industries configured</div>
    <div class="kpi-value"><strong style="font-size:22px;"><?php echo esc_html( number_format_i18n( count($inds) ) ); ?></strong></div>
    <p class="hint">Listele de industrii care alimentează generarea.</p>
  </div>

  <!-- Auto-Schedule Summary -->
  <div class="card" style="grid-column: span 6;">
    <h3 style="margin-bottom:8px;">Automation & Schedule</h3>
    <div class="row">
      <div class="col" style="grid-column: span 6;">
        <label>Articles per day</label>
        <div><strong><?php echo esc_html( intval($opts['articles_per_day'] ?? 1) ); ?></strong></div>
      </div>
      <div class="col" style="grid-column: span 6;">
        <label>Run times</label>
        <div><strong><?php echo esc_html( $opts['run_times'] ?? '09:00' ); ?></strong></div>
      </div>
    </div>
    <p class="hint mt-1">
      Auto-publish: <strong><?php echo esc_html( ($opts['auto_publish'] ?? '') === 'enabled' ? 'Enabled' : 'Disabled' ); ?></strong>.
      Poți schimba din <a href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-settings') ); ?>">Settings</a>.
    </p>
  </div>

  <!-- Industries List -->
  <div class="card" style="grid-column: span 6;">
    <h3 style="margin-bottom:8px;">Industries</h3>
    <?php if ( ! empty($inds) ): ?>
      <p><?php echo esc_html( implode(', ', $inds) ); ?></p>
    <?php else: ?>
      <p class="hint">Nu ai setat încă industriile. Mergi la <a href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-settings') ); ?>">Settings</a> și adaugă (CSV).</p>
    <?php endif; ?>
  </div>

  <!-- Quick Actions -->
  <div class="card" style="grid-column: span 12;">
    <h3 style="margin-bottom:8px;">Quick Actions</h3>
    <p>
      <a class="button button-primary" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-generate') ); ?>">✍️ Generate Article</a>
      <a class="button" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-seo') ); ?>">🔍 Keyword Planner</a>
      <a class="button" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-woo') ); ?>">🛒 Woo Bulk</a>
      <a class="button" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-settings') ); ?>">⚙️ Settings</a>
    </p>
  </div>

  <!-- Recent Posts -->
  <div class="card" style="grid-column: span 12;">
    <h3 style="margin-bottom:8px;">Recent Posts</h3>
    <?php if ( ! empty($recent_posts) ): ?>
      <table class="widefat striped">
        <thead>
          <tr>
            <th>Title</th>
            <th>Status</th>
            <th>Date</th>
            <th>Edit</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $recent_posts as $p ): ?>
            <tr>
              <td><?php echo esc_html( get_the_title($p->ID) ); ?></td>
              <td><?php echo esc_html( get_post_status($p->ID) ); ?></td>
              <td><?php echo esc_html( get_the_date( get_option('date_format').' '.get_option('time_format'), $p->ID ) ); ?></td>
              <td><a class="button" href="<?php echo esc_url( get_edit_post_link($p->ID) ); ?>">Edit</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="hint">Nu există articole recente.</p>
    <?php endif; ?>
  </div>

</div>
