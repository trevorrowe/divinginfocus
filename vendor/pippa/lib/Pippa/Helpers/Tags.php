<?php

namespace Pippa\Helpers;

class Tags extends \Pippa\Helper {

  public function css_tag($asset, $opts = array()) {
    if($asset[0] == '/' or preg_match('#^https?://#', $asset, $matches))
      $url = $asset;
    else
      $url = "/stylesheets/$asset.css";
    $media = isset($opts['media']) ? $opts['media'] : 'screen';
    return "<link href='$url' media='$media' rel='stylesheet' type='text/css' />";
  }

  public function js_tag($asset) {

    $url = $asset;
    if(!str_ends_with($url, '.js'))
      $url .= '.js';

    if($url[0] != '/' && !preg_match('#^https?://#', $asset, $matches))
      $url = "/javascripts/$url";

    return "<script src='$url' type='text/javascript'></script>";
  }

  public function tag($name, $content = null, $attributes = array()) {

    $self_closing = in_array($name, array(
      'meta', 'img', 'link', 'script', 'br', 'hr',
    ));

    # build the attributes
    $attr = array();
    foreach($attributes as $key => $value)
      if(!is_null($value)) {
        $value = htmlspecialchars($value, ENT_QUOTES);
        $attr[] = "$key='$value'";
      }
    $attr = empty($attr) ? '' : ' ' . implode(' ', $attr);

    if(is_array($content))
      $content = implode('', $content); 

    return ($self_closing && !$content)? 
      "<$name$attr />" : 
      "<$name$attr>$content</$name>";
  }

  public function link_to($label, $url, $opts = array()) {

    $opts['href'] = url($url);

    if(isset($opts['confirm']) && $opts['confirm']) {
      $msg = $opts['confirm'];
      if($msg === true)
        $msg = 'Are your sure?';
      else
        $msg = str_replace('\'', '\\\'', $msg);
      unset($opts['confirm']);
      $opts['onclick'] = "return confirm('$msg');";
    }

    return $this->tag('a', $label, $opts);
  }

  public function get_opt($opts, $key, $default) {
    return array_key_exists($key, $opts) ? $opts[$key] : $default;
  }

  public function img_tag($url, $opts = array()) {
    $opts['src'] = $url;
    if(!isset($opts['alt']))
      $opts['alt'] = $url;
    return $this->tag('img', null, $opts);
  }

}
