<?php
namespace WhatsPro\Premium\Services;
class ABAssigner {
  public static function pick(array $variants, int $contactId): array {
    $sum = array_sum(array_map(fn($v)=> (int)($v['weight']??0), $variants)) ?: 100;
    $roll = (crc32('wpp|'.$contactId) % $sum) + 1; $acc=0;
    foreach($variants as $v){ $acc += (int)($v['weight']??0); if($roll <= $acc) return $v; }
    return $variants[0];
  }
}
