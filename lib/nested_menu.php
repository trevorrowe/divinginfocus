<?php

function nested_menu($config_tree, $opts = array()) {

  $over = 'this.className = this.className.match(/current/) ? "current section hover" : "section hover";';
  $out = 'this.className = this.className.match(/current/) ? "current section" : "section";';

  $sections = array();
  foreach($config_tree as $section) {
    $current = '';
    $li = array("<li class='{$current}section' onmouseover='$over' onmouseout='$out'>");
    $li[] = link_tag($section['label'], $section['url']);
    if(!empty($section['links'])) {
      $li[] = '<ul>';
      foreach($section['links'] as $link_label => $link_url)
        $li[] = tag('li', link_tag($link_label, $link_url), array(
          'class' => 'menu_subsection'
        ));
      $li[] = '</ul>';
    }
    $li[] = '</li>';
    $sections[] = implode('', $li);
  }
  return tag('ul', $sections, $opts);
}

#echo nested_menu(array(
#  array(
#    'token' => 'users',
#    'label' => 'Users',
#    'url'   => array('controller' => 'admin/users'),
#    'links' => array(
#      'All' => array('controller' => 'admin/users'),
#      'Admins' => array('controller' => 'admin/users', 'action' => 'admins'),
#      'Disabled' => array('controller' => 'admin/users', 'action' => 'disabled'),
#    ),
#  ),
#  array(
#    'token' => 'blogs',
#    'label' => 'Blogs',
#    'url'   => array('controller' => 'admin/blogs'),
#    'links' => array(),
#  )), array('id' => 'menu', 'class' => 'nested_menu'))
