<?php
namespace WhatsPro\Premium\Services;
class Consent {
  public static function is_out(array $c): bool {
    return ($c['consent'] ?? 'unknown')==='opted_out' || (!empty($c['dnd_until']) && strtotime($c['dnd_until'])>time());
  }
}
