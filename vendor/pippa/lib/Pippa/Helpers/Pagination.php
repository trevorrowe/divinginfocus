<?php

namespace Pippa\Helpers;

class Pagination extends \Pippa\Helper {

  public function paginate($collection, $opts = array()) {
    return $this->link_pagination($collection);
  }

  public function link_pagination($collection, $opts = array()) {

    if($collection->pages == 1)
      return '';

    $links = array();
    $links[] = $this->link_to_prev_page($collection, $opts);
    $links = array_merge($links, $this->windowed_links($collection, $opts));
    $links[] = $this->link_to_next_page($collection, $opts);
    return $this->tag('div', $links, array('class' => 'pagination'));
  }

  public function link_to_prev_page($collection, $opts = array()) {
    $opts['label'] = '&laquo; Previous';
    $this->append_class_name($opts, 'prev_page');
    return $this->page_link_or_span($collection, $collection->prev_page, $opts);
  }

  public function link_to_next_page($collection, $opts = array()) {
    $opts['label'] = 'Next &raquo;';
    $this->append_class_name($opts, 'next_page');
    return $this->page_link_or_span($collection, $collection->next_page, $opts);
  }

  protected function page_link_or_span($collection, $page, $opts = array()) {
    $label = $this->get_opt($opts, 'label', $page);
    if($page && $page != $collection->page) {
      $url = url($this->params->merge(array('page' => $page)));
      return $this->link_to($label, $url, $opts);
    } else if($page) {
      $this->append_class_name($opts, 'current');
      return $this->tag('span', $label, $opts);
    } else {
      $this->append_class_name($opts, 'disabled');
      return $this->tag('span', $label, $opts);
    }
  }

  protected function windowed_links($collection, $opts = array()) {

    $links = array();

    $prev_page = 0;
    $visible = $this->visible_page_numbers($collection, $opts);
    foreach($visible as $page) {
      if($prev_page != $page - 1)
        $links[] = $this->tag('span', '...', array('class' => 'gap'));
      $links[] = $this->page_link_or_span($collection, $page);
      $prev_page = $page;
    }

    return $links;

  }

  protected function visible_page_numbers($collection, $opts = array()) {

    $page = $collection->page;
    $pages = $collection->pages;

    $inner_window = $this->get_opt($opts, 'inner_window', 3);
    $outer_window = $this->get_opt($opts, 'outer_window', 1);

    $window_from = $page - $inner_window;
    $window_to = $page + $inner_window;

    if($window_to > $pages) {
      $window_from -= $window_to - $pages;
      $winfow_to = $pages;
    }

    if($window_from < 1) {
      $window_to += 1 - $window_from;
      $window_from = 1;
      if($window_to > $pages)
        $window_to = $pages;
    }

    $visible = array();
    for($i = 1; $i <= $pages; ++$i)
      $visible[] = $i;

    $left_gap = array();
    for($i = 2 + $outer_window; $i < $window_from; ++$i)
      $left_gap[] = $i;

    $right_gap = array();
    for($i = $window_to + 1; $i < ($pages - $outer_window); ++$i)
      $right_gap[] = $i;

    if(!empty($left_gap) && end($left_gap) - $left_gap[0] > 1)
      $visible = array_diff($visible, $left_gap);
    
    if(!empty($right_gap) && end($right_gap) - $right_gap[0] > 1)
      $visible = array_diff($visible, $right_gap);

    return $visible;
  }

}
