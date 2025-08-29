<?php
namespace WhatsPro\Premium\Api;
use WP_REST_Request; use WP_REST_Response;
class WABA {
  public static function register(){
    register_rest_route('whatspro/v1','/waba/test',['methods'=>'POST','permission_callback'=>[__CLASS__,'cap'],'callback'=>[__CLASS__,'test']]);
  }
  public static function cap(){ return current_user_can('manage_options'); }
  public static function test(WP_REST_Request $r){
    $phone = get_option('wpp_waba_phone_id'); $token = get_option('wpp_waba_token');
    if(!$phone || !$token) return new WP_REST_Response(['ok'=>false,'note'=>'missing creds (simulated send will be used)'],200);
    $resp = wp_remote_get('https://graph.facebook.com/v20.0/'.rawurlencode($phone), ['headers'=>['Authorization'=>'Bearer '.$token], 'timeout'=>15]);
    if(is_wp_error($resp)) return new WP_REST_Response(['ok'=>false,'error'=>$resp->get_error_message()],200);
    return new WP_REST_Response(['ok'=>wp_remote_retrieve_response_code($resp)===200],200);
  }
}
