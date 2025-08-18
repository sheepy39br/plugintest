<?php
/**
 * Plugin Name: AI Blog Generator PRO
 * Description: Generator automat de articole + imagini + SEO + WooCommerce bulk, cu programare și auto-publish.
 * Version: 1.0.0
 * Author: AI Dev
 * Text Domain: ai-blog-generator-pro
 */

if ( ! defined('ABSPATH') ) exit;

define('AI_BGPRO_VERSION', '1.0.0');
define('AI_BGPRO_SLUG', 'ai-blog-generator');
define('AI_BGPRO_DIR', plugin_dir_path(__FILE__));
define('AI_BGPRO_URL', plugin_dir_url(__FILE__));

require_once AI_BGPRO_DIR . 'includes/class-ai-bgpro-admin.php';
require_once AI_BGPRO_DIR . 'includes/class-ai-bgpro-api.php';
require_once AI_BGPRO_DIR . 'includes/class-ai-bgpro-cron.php';

class AI_Blog_Generator_Pro {
  public function __construct() {
    add_action('admin_menu', [ $this, 'register_admin_page' ]);
    add_action('admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ]);

    // Handlers formulare admin
    add_action('admin_post_ai_bgpro_save_settings', [ $this, 'save_settings' ]);
    add_action('admin_post_ai_bgpro_generate_now', [ $this, 'generate_now' ]);
    add_action('admin_post_ai_bgpro_queue_keyword', [ $this, 'queue_keyword' ]);
    add_action('admin_post_ai_bgpro_run_bulk_woo', [ $this, 'run_bulk_woo' ]);
    add_action('admin_post_ai_bgpro_test_openai', [ $this, 'test_openai' ]);

    // Cron
    AI_BGPRO_Cron::register_events();
  }

  public function register_admin_page() {
    $hook = add_menu_page(
      __('AI Blog Generator PRO','ai-blog-generator-pro'),
      __('AI Blog Generator','ai-blog-generator-pro'),
      'manage_options',
      AI_BGPRO_SLUG,
      [ $this, 'render_admin' ],
      'dashicons-art',
      58
    );
    // Salvăm hook-ul pentru a încărca stilurile doar aici
    add_action("load-$hook", function() use ($hook){
      $GLOBALS['ai_bgpro_page_hook'] = $hook;
    });
  }

  public function enqueue_admin_assets($hook) {
    if ( empty($GLOBALS['ai_bgpro_page_hook']) || $hook !== $GLOBALS['ai_bgpro_page_hook'] ) return;
    wp_enqueue_style('ai-bgpro-modern', AI_BGPRO_URL.'assets/style-modern.css', [], AI_BGPRO_VERSION);
    wp_enqueue_script('ai-bgpro-admin', AI_BGPRO_URL.'assets/admin.js', ['jquery'], AI_BGPRO_VERSION, true);
    wp_localize_script('ai-bgpro-admin', 'AI_BGPRO', [
      'ajax' => admin_url('admin-ajax.php'),
      'nonce'=> wp_create_nonce('ai_bgpro_nonce')
    ]);
  }

  public function render_admin() {
    if ( ! current_user_can('manage_options') ) return;

    $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
    // Opțiuni persistente
    $opts = get_option('ai_bgpro_options', [
      'openai_key'   => '',
      'unsplash_key' => '',
      'articles_per_day' => 3,
      'run_times'    => '09:00,14:00,20:00',
      'auto_publish' => 'enabled',
      'industries'   => ['Fitness','Beauty','Technology','Travel','Finance'],
      'theme'        => 'dark'
    ]);

    echo '<div class="ai-bgpro-app" data-theme="'.esc_attr($opts['theme']).'">';
    AI_BGPRO_Admin::topbar($tab);
    echo '<div class="'.($tab==='dashboard'?'ai-bgpro-container ai-bgpro-dashboard':'ai-bgpro-container').'">';
    switch ($tab) {
      case 'generate':  AI_BGPRO_Admin::view_generate($opts); break;
      case 'seo':       AI_BGPRO_Admin::view_seo($opts); break;
      case 'woo':       AI_BGPRO_Admin::view_woo($opts); break;
      case 'settings':  AI_BGPRO_Admin::view_settings($opts); break;
      default:          AI_BGPRO_Admin::view_dashboard($opts); break;
    }
    echo '</div></div>';
  }

  public function save_settings() {
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    check_admin_referer('ai_bgpro_save_settings');

    $opts = get_option('ai_bgpro_options', []);
    $opts['openai_key']   = sanitize_text_field($_POST['openai_key'] ?? '');
    $opts['openai_project_id'] = sanitize_text_field($_POST['openai_project_id'] ?? ($opts['openai_project_id'] ?? ''));
    $opts['provider'] = in_array($_POST['provider'] ?? ($opts['provider'] ?? 'openai'), ['openai','groq','gemini','deepseek'], true) ? ($_POST['provider'] ?? 'openai') : 'openai';
    $opts['groq_key'] = sanitize_text_field($_POST['groq_key'] ?? ($opts['groq_key'] ?? ''));
    $opts['gemini_key'] = sanitize_text_field($_POST['gemini_key'] ?? ($opts['gemini_key'] ?? ''));
    $opts['deepseek_key'] = sanitize_text_field($_POST['deepseek_key'] ?? ($opts['deepseek_key'] ?? ''));
    $opts['model_openai'] = sanitize_text_field($_POST['model_openai'] ?? ($opts['model_openai'] ?? 'gpt-4o-mini'));
    $opts['model_groq'] = sanitize_text_field($_POST['model_groq'] ?? ($opts['model_groq'] ?? 'llama-3.1-70b-versatile'));
    $opts['model_gemini'] = sanitize_text_field($_POST['model_gemini'] ?? ($opts['model_gemini'] ?? 'gemini-1.5-flash'));
    $opts['model_deepseek'] = sanitize_text_field($_POST['model_deepseek'] ?? ($opts['model_deepseek'] ?? 'deepseek-chat'));
    $opts['unsplash_key'] = sanitize_text_field($_POST['unsplash_key'] ?? '');
    $opts['articles_per_day'] = max(1, intval($_POST['articles_per_day'] ?? 3));
    $opts['run_times']    = sanitize_text_field($_POST['run_times'] ?? '09:00');
    $opts['auto_publish'] = in_array($_POST['auto_publish'] ?? 'enabled', ['enabled','disabled'], true) ? $_POST['auto_publish'] : 'enabled';
    $opts['theme']        = in_array($_POST['theme'] ?? 'dark', ['dark','light'], true) ? $_POST['theme'] : 'dark';

    // Industries from multiselect or fallback CSV
    if (isset($_POST['industries']) && is_array($_POST['industries'])) {
      $inds = array_map('sanitize_text_field', $_POST['industries']);
      $inds = array_values(array_unique(array_filter(array_map('trim',$inds))));
      $opts['industries'] = $inds;
    } else {
      $industries_raw = sanitize_text_field($_POST['industries_raw'] ?? '');
      $inds = array_filter(array_map('trim', explode(',', $industries_raw)));
      if (!empty($inds)) $opts['industries'] = $inds;
    }

    update_option('ai_bgpro_options', $opts);
    wp_redirect( admin_url('admin.php?page='.AI_BGPRO_SLUG.'&tab=settings&updated=1') );
    exit;
  }

  public function generate_now() {
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    check_admin_referer('ai_bgpro_generate_now');

    $title    = sanitize_text_field($_POST['title'] ?? '');
    $keywords = sanitize_text_field($_POST['keywords'] ?? '');
    $tone     = sanitize_text_field($_POST['tone'] ?? 'Informative');
    $length   = sanitize_text_field($_POST['length'] ?? 'Medium');
    $industry = sanitize_text_field($_POST['industry'] ?? '');
    $opts     = get_option('ai_bgpro_options', []);

    $post_id = AI_BGPRO_API::generate_article_and_publish($title, $keywords, $tone, $length, $industry, $opts);
    $dest = admin_url('post.php?action=edit&post='.$post_id);
    wp_redirect($dest); exit;
  }

  public function queue_keyword() {
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    check_admin_referer('ai_bgpro_queue_keyword');

    $kw = sanitize_text_field($_POST['keyword'] ?? '');
    $q  = get_option('ai_bgpro_queue', []);
    $q[] = [
      'keyword' => $kw,
      'added'   => current_time('mysql'),
      'status'  => 'queued'
    ];
    update_option('ai_bgpro_queue', $q);
    wp_redirect( admin_url('admin.php?page='.AI_BGPRO_SLUG.'&tab=seo&queued=1') );
    exit;
  }

  public function run_bulk_woo() {
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    check_admin_referer('ai_bgpro_run_bulk_woo');

    $opts = get_option('ai_bgpro_options', []);
    $count = AI_BGPRO_API::generate_bulk_product_descriptions($opts, 20); // procesează max 20 pe run
    wp_redirect( admin_url('admin.php?page='.AI_BGPRO_SLUG.'&tab=woo&processed=' . intval($count)) );
    exit;
  }

  public function test_openai() {
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    check_admin_referer('ai_bgpro_test_openai');
    $key = sanitize_text_field($_POST['openai_key'] ?? '');
    if (empty($key)) {
      $opts = get_option('ai_bgpro_options', []);
      $key = $opts['openai_key'] ?? '';
    }
    $msg = '';
    $ok  = false;
    if (empty($key)) {
      $msg = 'No OpenAI key provided or saved.';
    } else {
      $body = [
        'model' => 'gpt-3.5-turbo-0125',
        'messages' => [
          ['role'=>'system','content'=>'You are a ping service. Reply with OK.'],
          ['role'=>'user','content'=>'Say OK']
        ],
        'max_tokens' => 3,
        'temperature' => 0
      ];
      $res = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
          'Content-Type'  => 'application/json',
          'Authorization' => 'Bearer '.$key
        ],
        'timeout' => 25,
        'body'    => wp_json_encode($body)
      ]);
      if (is_wp_error($res)) {
        $msg = 'WP Error: ' . $res->get_error_message();
      } else {
        $code = wp_remote_retrieve_response_code($res);
        $body_txt = wp_remote_retrieve_body($res);
        if ($code === 200) {
          $ok = true;
          $data = json_decode($body_txt, true);
          $reply = $data['choices'][0]['message']['content'] ?? '';
          $msg = 'Success. Model replied: ' . substr(trim($reply),0,30);
        } else {
          $err = '';
          $j = json_decode($body_txt, true);
          if (isset($j['error']['message'])) $err = $j['error']['message'];
          $msg = 'HTTP ' . $code . ($err ? (' — ' . $err) : '');
        }
      }
    }
    set_transient('ai_bgpro_test_msg', $msg, 60);
    set_transient('ai_bgpro_test_ok', $ok ? '1' : '0', 60);
    wp_redirect( admin_url('admin.php?page=' . AI_BGPRO_SLUG . '&tab=settings&openai_test=1') );
    exit;
  }

}


// === Global: add OpenAI-Project header when using sk-proj-* ===
add_filter('http_request_args', function($args, $url){
  if (strpos($url, 'api.openai.com') !== false) {
    $opts = get_option('ai_bgpro_options', []);
    $key  = isset($opts['openai_key']) ? trim($opts['openai_key']) : '';
    $proj = isset($opts['openai_project_id']) ? trim($opts['openai_project_id']) : '';
    if ($key && strpos($key, 'sk-proj-') === 0 && $proj) {
      $args['headers']['OpenAI-Project'] = $proj;
    }
  }
  return $args;
}, 10, 2);


add_action('wp_ajax_ai_bgpro_test_openai_ajax', function(){
  if ( ! current_user_can('manage_options') ) wp_send_json(['ok'=>False,'error'=>'Forbidden'], 403);
  check_ajax_referer('ai_bgpro_nonce','nonce');
  $opts = get_option('ai_bgpro_options', []);
  $key  = trim($opts['openai_key'] ?? '');
  $proj = trim($opts['openai_project_id'] ?? '');
  if (!$key) wp_send_json(['ok'=>false,'error'=>'No OpenAI key saved.'],400);
  $headers = [
    'Authorization' => 'Bearer '.$key,
    'Content-Type'  => 'application/json'
  ];
  if (strpos($key,'sk-proj-') === 0 && $proj) {
    $headers['OpenAI-Project'] = $proj;
  }
  $resp = wp_remote_post('https://api.openai.com/v1/chat/completions',[
    'timeout'=>45,
    'headers'=>$headers,
    'body'=> wp_json_encode([
      'model'=>'gpt-4o-mini',
      'messages'=>[['role'=>'user','content'=>'pong?']],
      'max_tokens'=>5,
      'temperature'=>0
    ])
  ]);
  if (is_wp_error($resp)) wp_send_json(['ok'=>false,'error'=>$resp->get_error_message()],500);
  $code = wp_remote_retrieve_response_code($resp);
  $body = wp_remote_retrieve_body($resp);
  if ($code !== 200) wp_send_json(['ok'=>false,'error'=>'HTTP '.$code.': '.substr($body,0,200)],$code);
  $data = json_decode($body,true);
  $text = trim($data['choices'][0]['message']['content'] ?? '');
  $model = $data['model'] ?? 'unknown';
  wp_send_json(['ok'=> (stripos($text,'pong')!==false), 'model'=>$model]);
});

new AI_Blog_Generator_Pro();

