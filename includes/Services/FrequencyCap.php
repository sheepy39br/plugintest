<?php
namespace WhatsPro\Premium\Services;
class FrequencyCap {
  public static function allow(int $cid, int $max, string $window='P1D'): bool {
    if($max<=0) return true;
    global $wpdb; $e=$wpdb->prefix.'wpp_events';
    $since = (new \DateTimeImmutable('now', wp_timezone()))->sub(new \DateInterval($window))->format('Y-m-d H:i:s');
    $cnt = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $e WHERE contact_id=%d AND type='msg_out' AND occurred_at >= %s", $cid, $since));
    return $cnt < $max;
  }
}
