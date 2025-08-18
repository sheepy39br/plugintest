<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AI_BGPro_License {

    public function __construct() {
        add_action('admin_init', [$this,'check_license']);
    }

    public function check_license() {
        $opts = get_option('ai_bgpro_options');
        $key = $opts['license'] ?? '';
        if(!$key){
            add_action('admin_notices', function(){
                echo '<div class="notice notice-warning"><p>⚠️ AI Blog PRO: Nu ai setat licența. Pluginul poate fi limitat.</p></div>';
            });
        }
        // aici poți face request la serverul tău de validare
    }
}
