<?php

namespace Pippa;

class Logger {

  protected $path;

  protected $stream;

  public function __construct($path) {
    $this->path = $path;
    $url = "file://$path";
    if(!$this->stream = @fopen($url, 'a', false))
      throw new Exception('Unable to write to log: ' . $this->path);
  }

  public function write($msg) {
    # TODO : rework this, not a fan of the @ infront of fwrite
    if(false === @fwrite($this->stream, $msg . "\n"))
      throw new Exception('Unable to write to log: ' . $this->path);
  }

  public function log($msg) {
    $this->write($msg);
  }
  
  public function request($request) {
    $cntl = $request->params['controller'];
    $actn = $request->params['action'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = strftime('%Y-%m-%d %T');
    $method = $request->method;
    $msg = "\nProcessing $cntl#$actn (for $ip at $time) [$method]";
    $this->write($msg);
  }

  public function timing($seconds) {
    $time = self::format_seconds($seconds);
    $status = 200;
    $url = Request::get_http_request()->url;
    $memory = format_bytes(memory_get_peak_usage(true));
    $msg = "Completed in $time using $memory | $status [$url]";
    $this->write($msg);
  }

  protected static function format_seconds($seconds) {
    $milli = round($seconds * 10000.0) / 10.0;
    switch(true) {
      case $milli < 1000: 
        return sprintf("%.1fms", $milli); 
      case $milli < (1000 * 60): 
        return sprintf("%.2f seconds", $milli / 1000); 
      default:
        $mins = floor(($milli / 1000) / 60);
        $seconds = ($milli - $mins * 1000 * 60) / 1000;
        return sprintf("%d mins %.2f seconds", $mins, $seconds);
    }
  }

}
