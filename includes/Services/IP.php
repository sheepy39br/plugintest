<?php
namespace WhatsPro\Premium\Services;
class IP {
  public static function unpack($bin){ if(!$bin) return null;
    if (strlen($bin)===4) return long2ip(unpack('N',$bin)[1]);
    if (strlen($bin)===16 && function_exists('inet_ntop')) return inet_ntop($bin);
    return null;
  }
}
