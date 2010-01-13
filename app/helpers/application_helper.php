<?php

function text_field($obj, $attr, $opts = array()) {
  $id = isset($opts['id']) ? $opts['id'] : form_field_id($obj, $attr);
  $name = form_field_name($obj, $attr);
  $value = $obj->attribute_before_type_cast($attr);
  return tag('input', null, array(
    'type' => 'text',
    'id' => $id,
    'name' => $name,
    'value' => $value,
  ));
}

function password_field($obj, $attr, $opts = array()) {
  $id = isset($opts['id']) ? $opts['id'] : form_field_id($obj, $attr);
  $name = form_field_name($obj, $attr);
  $value = $obj->attribute_before_type_cast($attr);
  return tag('input', null, array(
    'type' => 'password',
    'id' => $id,
    'name' => $name,
    'value' => $value,
  ));
}

function checkbox_field($obj, $attr, $opts = array()) {

  $id = isset($opts['id']) ? $opts['id'] : form_field_id($obj, $attr);
  $name = form_field_name($obj, $attr);
  $checked = $obj->$attr ? 'checked' : null;

  $hidden = tag('input', null, array(
    'type' => 'hidden',
    'name' => $name,
    'value' => '0',
  ));

  $checkbox = tag('input', null, array(
    'type' => 'checkbox',
    'id' => $id,
    'name' => $name,
    'value' => '1',
    'checked' => $checked,
  ));

  return $hidden . $checkbox;
}

function form_label($obj, $attr, $opts = array()) {

  $title = form_field_title($obj, $attr);

  if(isset($opts['required']) && $opts['required'])
    $title = tag('span', '*', array('class' => 'required_symbol')) . $title;
    
  return tag('label', $title, array(
    'for' => form_field_id($obj, $attr),
  ));
}

function form_field($type, $obj, $attr, $opts = array()) {
  $name = '';
  $value = '';
  switch($type) {
    case 'text':
      return text_field($obj, $attr, $opts);
    case 'checkbox':
      return checkbox_field($obj, $attr, $opts);
    case 'password':
      return password_field($obj, $attr, $opts);
    default:
      throw new Exception("unhandled form_field type `$type`");
  }
}

function form_errors($obj, $attr) {
  $errors = $obj->errors->on($attr);
  if(empty($errors))
    return '';
  $errors = implode(', ', $errors);
  return "<p class='error'>$errors</p>";
}

function form_row($type, $obj, $attr, $opts = array()) {

  $html = array();
  $html[] = form_label($obj, $attr, $opts);
  $html[] = form_field($type, $obj, $attr, $opts);

  if(isset($opts['hint']))
    $html[] = tag('div', $opts['hint'], array('class' => 'hint'));

  $errors = form_errors($obj, $attr, $opts);

  if($errors)
    $html[] = tag('div', $errors, array('class' => 'errors'));

  $css = array();
  if(isset($opts['required']) && $opts['required']) $css[] = 'required';
  if(isset($opts['class'])) $css[] = $opts['class'];
  if($errors) $css[] = 'invalid';
  $css[] = $type;
  $css[] = 'row';
  $css = implode(' ', array_unique($css));

  return tag('div', $html, array('class' => $css));

}

function text_row($obj, $attr, $opts = array()) {
  return form_row('text', $obj, $attr, $opts);
}

function checkbox_row($obj, $attr, $opts = array()) {
  return form_row('checkbox', $obj, $attr, $opts);
}

function password_row($obj, $attr, $opts = array()) {
  return form_row('password', $obj, $attr, $opts);
}

function submit_row($label = 'Submit', $opts = array()) {
  $opts['type'] = 'submit';
  $opts['value'] = $label;
  $submit = tag('input', null, $opts);
  return tag('div', $submit, array('class' => 'submit row'));
}

function form_field_title($obj, $attr) {
  return ucfirst($attr);
}

function form_field_id($obj, $attr) {
  return strtolower(get_class($obj) . "_$attr");
}

function form_field_name($obj, $attr) {
  return strtolower(get_class($obj) . "[$attr]");
}
