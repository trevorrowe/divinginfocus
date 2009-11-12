<?php

namespace Phml;

class Engine {

  protected $cache_dir;
  protected $cache = false;

  public function __construct($cache_dir, $options = array()) {
    $this->cache_dir = $cache_dir;
    if(isset($options['cache']))
      $this->cache = $options['cache'];
  }

  public function render($template_path) {
    $template = new Template($template_path);
    return (string) $template;
  }

}

class Template {

  protected $template_path;
  
  protected $indent = 0;
  protected $stack = array();
  protected $buffer = array();
  
  public function __construct($template_path) {
    # TODO : raise an exception if the template is not readable or not found
    $this->template_path = $template_path;
  }

  public function render() {
    $this->parse();
    return implode("\n", $this->buffer);
  }

  protected function parse() {

    $num = 0;
    $prev_line = NULL;

    $template = fopen($this->template_path, 'r');
    while(!feof($template)) {

      $line = fgets($template);
      echo $line;

      $line = new Line(++$num, $line);
      $this->check_indentation($prev_line, $line);
      $this->manage_stack($line);

      switch(true) {
        case $line->type == 'ignore':
          break;
        case $line->block_content():
          $this->buffer_str($line->indent, $line->opening());
          $this->stack[] = $line;
          break;
        default:
          $this->buffer_str($line->indent, (string) $line);
      }

      $prev_line = $line;

    }
    fclose($template);

    $this->empty_stack();

  }

  protected function check_indentation($prev_line, $line) {
    if($prev_line) {
      $max_indent = $prev_line->indent;
      $max_indent += $prev_line->block_content() ? 1 : 0;
    } else {
      $max_indent = 0;
    }
    if($line->indent > $max_indent)
      throw new Exception("invalid indentation on line {$line->num}");
  }

  protected function manage_stack(&$line) {
    $stack_count = count($this->stack);
    if($stack_count == 0) return;
    $top = $this->stack[$stack_count - 1];
    if($line->indent == $top->indent) {
      $this->buffer_str($top->indent, $top->closing());
      array_pop($this->stack);
    }
  }

  protected function empty_stack() {
#print_r($this->buffer);
#print_r($this->stack);
    $stack_count = count($this->stack);
    while($stack_count > 0) {
      $line = array_pop($this->stack);
      $this->buffer_str($line->indent, $line->closing());
      --$stack_count;
    }
  }

  protected function buffer_str($indent, $str) {
    $this->buffer[] = str_repeat('  ', $indent) . $str;
  }

  public function __toString() {
    return $this->render();
  }

}

class Line {

  public $num;
  public $indent;
  public $line;
  public $type;
  public $content = NULL;

  public function __construct($num, $line) {
    # TODO : validate the indentation is correct (2 spaces, not tabs, etc)
    $line = rtrim($line);
    $spaces = strspn($line, ' ');
    $line = ltrim($line);
    $this->num = $num;
    $this->indent = $spaces / 2;
    $this->line = $line;
    $this->determine_type();
  }

  public function block_content() {
    return $this->content == NULL;
  }

  public function opening() {
    switch($this->type) {
      case 'html_comment':  
        return '<!-- ';
    }
  }

  public function closing() {
    switch($this->type) {
      case 'html_comment':  
        return ' -->';
    }
  }

  protected function determine_type() {
    $line = $this->line;
    switch(true) {

      # phml comments and blank lines 
      case $this->begins_with('-#');
      case $line == '':kk
        $this->type = 'ignore';
        break;

      ## html comment
      case preg_match('/^\/\s*(.+)?$/', $line, $matches):
        $this->type = 'html_comment';
        $this->content = isset($matches[1]) ? $matches[1] : NULL;
        break;

      # html element

      case preg_match('/^%([a-zA-Z]+)(\s+(.+))?/', $line, $matches):
      #case $line[0] == '%':
      #case $line[0] == '#':
      #case $line[0] == '.':
        $this->type = 'html_elemnent';
        $this->content = isset($matches[3]) ? $matches[3] : NULL;
        break;

      # phml comment
      case $this->begins_with('-#'):
        break;

      # - if
      # - while
      # - foreach
      # - switch
      # - etc

      # interpreted as php
      case $line[0] == '-':
      case $line[0] == '=':
      case $line[0] == '~':
        break;

      # markup switch
      case $line[0] == ':':
        break;

      # static text
      default:
        $this->type = 'static';
        $this->content = $line;
        break;
    }
  }

  protected function begins_with($search) {
    return (strncmp($this->line, $search, strlen($search)) == 0);
  }

  public function __toString() {
    return $this->opening() . $this->content . $this->closing();
  }

}

$t = '/Users/trowe/projects/divinginfocus/app/views/index/index.html.phml';

$phml_engine = new Engine('foo');
$output = $phml_engine->render($t);
echo "\n============================================\n\n";
echo $output;
echo "\n\n";
