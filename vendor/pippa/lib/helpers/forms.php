<?php

# TODO : merge down opts into the basic form field elements as attributes

function submit_tag($label, $opts = array()) {
  $opts['type'] = 'submit';
  $opts['value'] = $label;
  append_class_name($opts, 'submit');
  return tag('input', null, $opts);
}

function submit_row($label, $opts = array()) {
  $row_opts = form_row_opts($opts);
  append_class_name($row_opts, 'submit');
  return form_row(submit_tag($label, $opts), $row_opts);
}

function checkbox_tag($name, $checked = false, $opts = array()) {
  $opts['type'] = 'checkbox';
  $opts['name'] = $name;
  $opts['value'] = isset($opts['value']) ? $opts['value'] : '1';
  $opts['checked'] = $checked ? 'checked' : null;
  append_class_name($opts, 'checkbox');
  append_default_form_field_id($opts, $name);
  return tag('input', null, $opts);
}

function checkbox($obj, $attr, $opts = array()) {
  $name = form_field_name($obj, $attr);
  $hidden = tag('input', null, array(
    'type' => 'hidden',
    'name' => $name,
    'value' => '0',
    'class' => 'hidden',
  ));
  $checkbox = checkbox_tag($name, $obj->$attr, $opts); 
  return $hidden . $checkbox;
}

function checkbox_row($obj, $attr, $opts = array()) {
  $row_opts = form_row_opts($opts);
  $row_opts['errors'] = errors_on($obj, $attr);
  $row_opts['label'] = titleize($attr);
  append_class_name($row_opts, 'checkbox');
  return form_row(checkbox($obj, $attr, $opts), $row_opts);
}

function text_field_tag($name, $value = null, $opts = array()) {
  $opts['type'] = 'text';
  $opts['name'] = $name;
  $opts['value'] = $value;
  append_class_name($opts, 'text');
  append_default_form_field_id($opts, $name);
  return tag('input', null, $opts);
}

function text_field($obj, $attr, $opts = array()) {
  $name = form_field_name($obj, $attr);
  $value = $obj->attribute_before_type_cast($attr);
  return text_field_tag($name, $value, $opts);
}

function text_field_row($obj, $attr, $opts = array()) {

  $row_opts = form_row_opts($opts);
  $row_opts['errors'] = errors_on($obj, $attr);
  $row_opts['label'] = titleize($attr);
  append_class_name($row_opts, 'text');

  $name = form_field_name($obj, $attr);
  $value = $obj->attribute_before_type_cast($attr);
  $field = text_field_tag($name, $value, $opts);

  return form_row($field, $row_opts);

}

function password_field_tag($name, $value = null, $opts = array()) {
  $opts['type'] = 'password';
  $opts['name'] = $name;
  $opts['value'] = $value;
  append_class_name($opts, 'password');
  append_default_form_field_id($opts, $name);
  return tag('input', null, $opts);
}

function password_field($obj, $attr, $opts = array()) {
  $name = form_field_name($obj, $attr);
  $value = $obj->attribute_before_type_cast($attr);
  return password_field_tag($name, $value, $opts);
}

function password_field_row($obj, $attr, $opts = array()) {

  $row_opts = form_row_opts($opts);
  $row_opts['errors'] = errors_on($obj, $attr);
  $row_opts['label'] = titleize($attr);
  append_class_name($row_opts, 'password');

  $name = form_field_name($obj, $attr);
  $value = $obj->attribute_before_type_cast($attr);
  $field = password_field_tag($name, $value, $opts);

  return form_row($field, $row_opts);

}

# Options: 
#
# * label
# * label_for
# * required
# * required_symbol
# * error
# * hint
# * class
#
function form_row($content, $opts = array()) {

  $html = array();

  # label
  if(isset($opts['label']) && $opts['label']) {
    $label = $opts['label'];
    if(isset($opt['required']) && $opts['required']) {
      $symbol = tag('span', '*', array('class' => 'required_symbol'));
      $label = "$symbol $label";
    }
    $for = isset($opts['label_for']) ? $opts['label_for'] : null;
    $html[] = tag('label', $label, array('for' => $for));
  }

  # content
  $html[] = $content;

  # error message
  if(isset($opts['error']) && $opts['error']) {
    $html[] = tag('p', $opts['error'], array('class' => $opts['error']));
  }

  # hint
  if(isset($opts['hint']) && $opts['hint']) {
    $html[] = tag('p', $opts['hint'], array('class' => 'hint'));
  }

  # row class names
  $css = array();
  if(isset($opts['error']) && $opts['error']) 
    $css[] = 'invalid';
  if(isset($opts['required']) && $opts['required']) 
    $css[] = 'required';
  if(isset($opts['class']) && $opts['class']) 
    $css[] = $opts['class'];
  $css[] = 'row';
  $css = implode(' ', $css);

  # return the row
  return tag('div', $html, array('class' => $css));

}

# private
function form_row_opts(&$opts) {
  $row_opts_names = array(
    'label' => false,
    'label_for' => false,
    'required' => false,
    'required_symbol' => false,
    'error' => false,
    'hint' => false,
    'row_class' => 'class',
  );
  $row_opts = array();
  foreach($row_opts_names as $row_opt_name => $rename) {
    if(isset($opts[$row_opt_name])) {
      $rename = $rename ? $rename : $row_opt_name;
      $row_opts[$rename] = $opts[$row_opt_name];
      unset($opts[$row_opt_name]);
    }
  }
  return $row_opts;
}

# private
function errors_on($obj, $attr) {
  $errors = $obj->errors->on($attr);
  return empty($errors) ? null : implode(', ', $errors);
}

# private
function form_field_id($form_field_name) {
  return preg_replace('/\[(.*)\]/', '_$1', $form_field_name);
}

# private
function form_field_name($obj, $attr) {
  return strtolower(get_class($obj) . "[$attr]");
}

# private
function append_default_form_field_id(&$opts, $form_field_name) {
  if(!array_key_exists('id', $opts))
    $opts['id'] = form_field_id($form_field_name);
}

# private
function append_class_name(&$opts, $class) {
  if(isset($opts['class'])) {
    if(!preg_match("/(^|\s+){$opts['class']}(\s+|$)/", $opts['class']))
      $opts['class'] = "{$opts['class']} $class";
  } else {
    $opts['class'] = $class;
  }
}

# TODO : move this function out of the framework an into app helper
function base_errors($obj) {
  $errors = errors_on($obj, 'base');
  return $errors ? tag('p', $errors, array('class' => 'errors')) : null;
}
