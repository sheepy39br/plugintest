<?php
/**
 * Settings View
 * Path: admin/views/settings.php
 * Configurații generale: API keys, tema UI, cron, licență.
 */
if ( ! defined('ABSPATH') ) exit;

$opts = get_option('ai_bgpro_options', [
  'openai_key'   => '',
  'unsplash_key' => '',
  'theme'        => 'dark',
  'cron_interval'=> 'daily',
]);
$license = get_option('ai_bgpro_license', []);
?>
<?php
// OpenAI test notice
if ( isset($_GET['openai_test']) ) {
  $ok  = get_transient('ai_bgpro_test_ok') === '1';
  $msg = get_transient('ai_bgpro_test_msg');
  delete_transient('ai_bgpro_test_ok');
  delete_transient('ai_bgpro_test_msg');
  echo '<div class="card" style="border-left:4px solid '.($ok?'#57c490':'#c25555').';"><strong>OpenAI Test ' . ($ok?'OK':'FAILED') . ':</strong> '.esc_html($msg).'</div>';
}
?>

<div id="ai-bgpro-wrap" class="ai-bgpro-app" data-theme="<?php echo esc_attr($opts['theme'] ?? 'dark'); ?>">
  <div class="ai-bgpro-container">

    <!-- Tabs vizuale -->
    <div class="ai-bgpro-topbar">
      <div class="tabs">
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro') ); ?>">Dashboard</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-generate') ); ?>">Generate</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-seo') ); ?>">SEO</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-woo') ); ?>">WooCommerce</a>
        <a class="tab active" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-settings') ); ?>">Settings</a>
      </div>
    </div>

    <!-- Form Settings -->
    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
      <?php wp_nonce_field('ai_bgpro_save_settings'); ?>
<input type="hidden" name="action" value="ai_bgpro_save_settings"/>
      <div class="card">
        <h2>API Keys</h2>
        <div class="row">
          <div class="col" style="grid-column:span 6;">
            <label>OpenAI API Key</label>
            <input type="password" name="openai_key" value="<?php echo esc_attr($opts['openai_key']); ?>" placeholder="sk-..." />
            <p class="hint">Obține cheia de la <a href="https://platform.openai.com" target="_blank">OpenAI</a>.</p>
          </div>
          <div class="col" style="grid-column:span 6;">
            <label>Unsplash Access Key</label>
            <input type="text" name="unsplash_key" value="<?php echo esc_attr($opts['unsplash_key']); ?>" placeholder="unsplash-key" />
            <p class="hint">Pentru imagini AI fallback. <a href="https://unsplash.com/developers" target="_blank">Creează cont</a>.</p>
          </div>
        </div>
      </div>

      <div class="card">
        <h2>Interface & Schedule</h2>
        <div class="row">
          <div class="col" style="grid-column:span 4;">
            <label>Theme</label>
            <select name="theme">
              <option value="light" <?php selected($opts['theme'],'light'); ?>>Light</option>
              <option value="dark" <?php selected($opts['theme'],'dark'); ?>>Dark</option>
            </select>
          </div>
          <div class="col" style="grid-column:span 4;">
            <label>Auto-Schedule Interval</label>
            <select name="cron_interval">
              <option value="hourly" <?php selected($opts['cron_interval'],'hourly'); ?>>Hourly</option>
              <option value="twicedaily" <?php selected($opts['cron_interval'],'twicedaily'); ?>>2 / day</option>
              <option value="daily" <?php selected($opts['cron_interval'],'daily'); ?>>Daily</option>
            </select>
          </div>
        </div>
      </div>

      <div class="card">
        <h2>License</h2>
        <div class="row">
          <div class="col" style="grid-column:span 8;">
            <label>License Key</label>
            <input type="text" name="ai_bgpro_license[key]" value="<?php echo esc_attr($license['key'] ?? ''); ?>" placeholder="XXXX-XXXX-XXXX" />
            <p class="hint">Introduceți cheia primită după achiziție.</p>
          </div>
          <div class="col" style="grid-column:span 4;">
            <label>Status</label>
            <div class="badge" style="margin-top:8px;">
              <?php echo !empty($license['status']) ? esc_html($license['status']) : 'Not activated'; ?>
            </div>
          </div>
        </div>
      </div>

      <p class="submit">
        <button type="submit" class="button button-primary">Save Settings</button>
      </p>
    </form>

  </div>
</div>

      <div class="card">
        <h2>Test OpenAI Connection</h2>
        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
          <?php wp_nonce_field('ai_bgpro_test_openai'); ?>
          <input type="hidden" name="action" value="ai_bgpro_test_openai"/>
          <div class="row">
            <div class="col" style="grid-column:span 8;">
              <label>Use this key (optional)</label>
              <input type="password" name="openai_key" placeholder="Leave empty to use saved key"/>
              <p class="hint">Dacă îl lași gol, testăm cheia salvată mai sus.</p>
            </div>
          </div>
          <button class="button button-primary">Test OpenAI</button>
        </form>
      </div>
