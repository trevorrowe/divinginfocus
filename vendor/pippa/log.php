<?php

namespace Pippa;

class Log {

  protected $path;
  protected $stream;

  public function __construct($path) {
    $this->path = $path;
    $url = "file://$path";
    if(!$this->stream = @fopen($url, 'a', false))
      throw new Exception('Unable to write to log: ' . $this->path);
  }

  public function write($msg) {
    if(false === @fwrite($this->stream, $msg . "\n"))
      throw new Exception('Unable to write to log: ' . $this->path);
  }
  
  public static function request($request) {
    $cntl = $request->params['controller'];
    $actn = $request->params['action'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = strftime('%Y-%m-%d %T');
    $method = $request->method;
    $msg = "\nProcessing $cntl#$actn (for $ip at $time) [$method]";
    App::$log->write($msg);
  }

  public static function db($query, $ms) {
    $reset = "\x1b[0m";
    $bold = "\x1b[1m";
    $color = cycle("\x1b[36m","\x1b[35m");
    $weight = cycle($bold, '');
    $time = '10ms';
    $msg = "  $color$bold[DB] ($time)$reset$weight $query$reset";
    App::$log->write($msg);
  }

  public static function timing($start, $stop) {
    $ms = round(($stop - $start) * 1000);
    $time = self::format_ms($ms);
    $status = 200;
    $url = Request::get_http_request()->url;
    $memory = format_bytes(memory_get_peak_usage(true));
    $msg = "Completed in $time using $memory | $status [$url]";
    App::$log->write($msg);
  }

  protected static function format_ms($ms) {
    switch(true) {
      case $ms < 1000: 
        return sprintf("%dms", floor($ms)); 
      case $ms < (1000 * 60): 
        return sprintf("%.2f seconds", $ms / 1000); 
      default:
        $mins = floor(($ms / 1000) / 60);
        $seconds = ($ms - $mins * 1000 * 60) / 1000;
        return sprintf("%d mins %.2f seconds", $mins, $seconds);
    }
  }

}
