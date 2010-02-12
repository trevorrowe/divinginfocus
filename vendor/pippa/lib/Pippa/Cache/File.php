<?php

namespace Pippa\Cache;

class File {

  protected $name;

  protected $caches = array();

  protected $cache_data = array();

  public function __construct($name) {
    $this->name = $name;
  }

  public function __get($key) {
    return $this->get($key);
  }

  public function set($key, $callback) {
    $this->caches[$key] = $callback;
  }

  public function get($key) {

    if(isset($this->cache_data[$key]))
      return $this->cache_data[$key];

    if(!isset($this->caches[$key]))
      throw new Exception("undefined cache: $key");

    $callback = $this->caches[$key];
    $this->cache_data[$key] = $callback();
    return $this->cache_data[$key];

  }

  public function warm() {
    foreach(array_keys($this->caches) as $key)
      $this->get($key);
    $this->write();
  }

  public function load() {
    # TODO : don't do anything unless app caching is enabled
    $this->read();
  }

  public function clear() {
    $this->cache_data = array();
    if(file_exists($this->path()))
      unlink($this->path());
  }

  protected function path() {
    return \App::root . "/tmp/cache/static/{$this->name}.cache";
  }

  protected function write() {
    # serialize it to disk
    $path = $this->path();
    if(!file_exists(dirname($path)))
      mkdir(dirname($path), 0777, true);
    file_put_contents($path, serialize($this->cache_data));
  }

  protected function read() {
    $this->cache_data = unserialize(file_get_contents($this->path()));
  }

}
