<?php

namespace Pippa;

class Log {

  protected $stream;

  protected static $logger;

  protected function __construct() {
    $url = 'file://' . App::root . '/log/' . App::env . '.log';
    if(!$this->stream = @fopen($url, 'a', false))
      throw new Exception("Unable to write to application log: $url");
  }

  public function write($msg) {
    if(false === @fwrite($this->stream, $msg . "\n"))
      throw new Exception("Unable to write to log");
  }

  public static function logger() {
    if(is_null(self::$logger))
      self::$logger = new Log();
    return self::$logger;
  }
  
  public static function request($request) {
    $cntl = $request->params['controller'];
    $actn = $request->params['action'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = strftime('%Y-%m-%d %T');
    $method = $request->method;
    $msg = "\nProcessing $cntl#$actn (for $ip at $time) [$method]";
    self::logger()->write($msg);
  }

  public static function db($query, $ms) {
    $reset = "\x1b[0m";
    $bold = "\x1b[1m";
    $color = cycle("\x1b[36m","\x1b[35m");
    $weight = cycle($bold, '');
    $time = '10ms';
    $msg = "  $color$bold[DB] ($time)$reset$weight $query$reset";
    self::logger()->write($msg);
  }

  public static function timing($start, $stop) {
    $ms = ($stop - $start) * 1000;
    $time = self::format_time($ms);
    $msg = "  \x1b[33m\x1b[1m[APP TIMING] ($time)\x1b[0m $ms";
    self::logger()->write($msg);
    $url = request_url();
    $status = 200;
    $msg = "Completed in $time ($ms) | $status [$url]";
    self::logger()->write($msg);
  }

  protected static function format_time($ms) {
    switch(true) {
      case $ms < 1000: 
        return sprintf("%d ms", floor($ms)); 
      case $ms < (1000 * 60): 
        return sprintf("%.2f seconds", $ms / 1000); 
      default:
        $mins = floor(($ms / 1000) / 60);
        $seconds = ($ms - $mins * 1000 * 60) / 1000;
        return sprintf("%d mins %.2f seconds", $mins, $seconds);
    }
  }

}
