<?php


class Route {
  
  protected $pattern;

  protected $defaults;

  protected $req;

  protected $regex;

  protected $capture_indexes;

  public function __construct($pattern, $opts = array()) {

    foreach(array('requirements', 'defaults') as $opt)
      $this->$opt = isset($opts[$opt]) ? $opts[$opt] : array();

    # move the format from the pattern into requirements
    if(preg_match('/^(.+)\.(\w+)$/', $pattern, $matches)) {
      $pattern = $matches[1];
      $this->requirements['format'] = $matches[2];
    }

    $this->pattern = trim($pattern, '/');

    $this->compile();
      
  }

  public function matches_request($request) {
    # match method
    # match format
    # match pattern
    # controller exists
  }

  private function compile() {

    $match_index = 0;

    $regex = array();
    foreach(explode('/', $this->pattern) as $segment) {

      # empty routes (root) dont contain any slashes
      if($segment == '')
        continue;

      # static route segment, this value is fixed and does not get captured
      if($segment[0] != ':') {
        $regex[] = $segment;
        $match_index += $this->count_captures($segment);
        continue;
      }

      # if we got this far there is a named segment (starts with a colon)
      # that needs to be captured

      # prune the leading : from the segment
      $segment = substr($segment, 1);

      # keep track of which regex-match-offset this segment will be
      $this->capture_indexes[++$match_index] = $segment;

      if(isset($this->requirements[$segment])) {

        $req = preg_replace('/\./', '[^/]', $this->requirements[$segment]);
        $match_index += $this->count_captures($req);
        $regex[] = "($req)";

      } else if($segment == 'controller') {

        $regex[] = '(\w[/\w]*)';

      } else if($segment == 'action') {

        $regex[] = '(\w+)';

      } else {

        $regex[] = '([^/]+)';

      }
    }

    $regex = impolode('/', $regex);
    $this->regex = "#^$regex$#";

  }

  private function count_captures() {
    return preg_match_all('/\(/', $str, $discard);
  }

}
