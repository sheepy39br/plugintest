<?php
namespace WhatsPro\Premium\Services;
class SendWindow {
  public static function can_send(array $contact, array $opts): array {
    $qh = $opts['quiet_hours'] ?? null; $tz = self::tz($contact, $opts);
    $now = new \DateTimeImmutable('now', $tz);
    if(!$qh) return ['ok'=>true];
    [$sH,$sM] = array_map('intval', explode(':', $qh['start'] ?? '22:00'));
    [$eH,$eM] = array_map('intval', explode(':', $qh['end'] ?? '08:00'));
    $start = $now->setTime($sH,$sM); $end = $now->setTime($eH,$eM);
    $in = $start <= $end ? ($now>=$start && $now<$end) : ($now>=$start || $now<$end);
    if(!$in) return ['ok'=>true];
    $next = $start <= $end ? $end : $end->modify('+1 day');
    return ['ok'=>false,'next'=>$next->format('Y-m-d H:i:s')];
  }
  private static function tz(array $contact, array $opts): \DateTimeZone {
    $tz = $contact['timezone'] ?? null;
    if($tz && in_array($tz, \DateTimeZone::listIdentifiers(), true)) return new \DateTimeZone($tz);
    return wp_timezone();
  }
}
