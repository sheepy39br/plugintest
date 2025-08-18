<?php
if ( ! defined('ABSPATH') ) exit;

class AI_BGPRO_Cron {

  public static function register_events(){
    add_action('ai_bgpro_cron_run', [__CLASS__, 'run_queue']);

    // Asigură un cron la fiecare 15 minute
    add_filter('cron_schedules', function($s){
      $s['ai_bgpro_15min'] = ['interval'=>900, 'display'=>'AI BG PRO 15 minutes'];
      return $s;
    });

    if ( ! wp_next_scheduled('ai_bgpro_cron_run') ) {
      wp_schedule_event(time()+300, 'ai_bgpro_15min', 'ai_bgpro_cron_run');
    }
  }

  public static function run_queue(){
    $opts = get_option('ai_bgpro_options', []);
    if ( empty($opts) ) return;

    $auto = ($opts['auto_publish'] ?? 'enabled') === 'enabled';
    $run_times = array_filter(array_map('trim', explode(',', $opts['run_times'] ?? '09:00')));

    // rulează doar la orele configurate
    $now = current_time('H:i');
    $match = in_array($now, $run_times, true);
    if ( ! $match ) return;

    $queue = get_option('ai_bgpro_queue', []);
    if (empty($queue)) return;

    $to_make = intval($opts['articles_per_day'] ?? 1);
    for ($i=0; $i<$to_make; $i++) {
      $item = array_shift($queue);
      if ( ! $item ) break;

      $keywords = $item['keyword'];
      $title = '';
      $tone = 'Informative';
      $length = 'Medium';
      $industry = $opts['industries'][ array_rand($opts['industries']) ] ?? 'General';

      $post_id = AI_BGPRO_API::generate_article_and_publish($title, $keywords, $tone, $length, $industry, $opts);
      if ($post_id && $auto) {
        wp_update_post([ 'ID'=>$post_id, 'post_status'=>'publish' ]);
      }
    }
    update_option('ai_bgpro_queue', $queue);
  }
}
