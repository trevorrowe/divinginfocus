<?php

function format_bytes($bytes, $precision = 2) {
  $kb = 1024.0;
  $mb = 1048576.0;
  $gb = 1073741824.0;
  $tb = 1099511627776.0;
  $pb = 1125899906842624.0;
  $eb = 1152921504606846976.0;
  $zb = 1180591620717411303424.0;
  $yb = 1208925819614629174706176.0;
  switch(true) {
    case $bytes < $kb:
      return sprintf("%d Bytes", $bytes);
    case $bytes < $mb:
      return sprintf("%.{$precision}f KB", $bytes / $kb);
    case $bytes < $gb:
      return sprintf("%.{$precision}f MB", $bytes / $mb);
    case $bytes < $tb:
      return sprintf("%.{$precision}f GB", $bytes / $gb);
    case $byte < $pb:
      return sprintf("%.{$precision}f TB", $bytes / $tb);
    case $byte < $eb:
      return sprintf("%.{$precision}f PB", $bytes / $pb);
    case $byte < $zb:
      return sprintf("%.{$precision}f EB", $bytes / $eb);
    case $byte < $yb:
      return sprintf("%.{$precision}f ZB", $bytes / $zb);
    default:
      return sprintf("%.{$precision}f YB", $bytes / $yb);
  }
}

function format_y_n($bool) {
  if(is_null($bool))
    return '';
  return $bool ? 'Y' : 'N';
}

function format_yes_no($bool) {
  if(is_null($bool))
    return '';
  return $bool ? 'Yes' : 'No';
}

function format_date($date, $format = '%Y-%m-%d') {
  if(is_null($date))
    return '';
  return strftime($format, $date->getTimestamp());
}

function format_datetime($datetime, $format = '%Y-%m-%d %T') {
  if(is_null($datetime))
    return '';
  return strftime($format, $datetime->getTimestamp());
}
