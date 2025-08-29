<?php
namespace WhatsPro\Premium\Api;
use WP_REST_Request; use WP_REST_Response;
class Settings {
  public static function register(){
    register_rest_route('whatspro/v1','/settings',['methods'=>'GET','permission_callback'=>[__CLASS__,'cap'],'callback'=>[__CLASS__,'get']]);
    register_rest_route('whatspro/v1','/settings',['methods'=>'POST','permission_callback'=>[__CLASS__,'cap'],'callback'=>[__CLASS__,'save']]);
  }
  public static function cap(){ return current_user_can('manage_options'); }
  public static function get(){ return new WP_REST_Response( get_option('wpp_settings', ['quiet_hours'=>null,'tz_mode'=>'site','freq_cap'=>['max'=>0,'window'=>'P1D']]), 200); }
  public static function save(WP_REST_Request $r){ update_option('wpp_settings', $r->get_json_params() ?: []); return new WP_REST_Response(['ok'=>true],200); }
}
