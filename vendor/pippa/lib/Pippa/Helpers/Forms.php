<?php

namespace Pippa\Helpers;

class Forms extends \Pippa\Helper {

  public function submit_tag($label, $opts = array()) {
    $opts['type'] = 'submit';
    $opts['value'] = $label;
    $this->append_class_name($opts, 'submit');
    return $this->tag('input', null, $opts);
  }

  public function submit_row($label, $opts = array()) {
    $row_opts = $this->form_row_opts($opts);
    $this->append_class_name($row_opts, 'submit');
    return $this->form_row($this->submit_tag($label, $opts), $row_opts);
  }

  public function hidden_field_tag($name, $value = null, $opts = array()) {
    $opts['type'] = 'hidden'; 
    $opts['value'] = $value;
    return $this->tag('input', null, $opts);
  }

  public function hidden_field($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $value = $obj->attribute_before_type_cast($attr);
    return $this->hidden_field_tag($name, $value, $opts);
  }

  public function file_field_tag($name, $opts = array()) {
    $opts['type'] = 'file';
    $opts['name'] = $name;
    $this->append_class_name($opts, 'file');
    $this->append_default_form_field_id($opts, $name);
    return $this->tag('input', null, $opts);
  }

  public function file_field($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    return $this->file_field_tag($name, $opts);
  }

  public function file_field_row($obj, $attr, $opts = array()) {

    $row_opts = $this->form_row_opts($opts);
    $row_opts['error'] = $this->errors_on($obj, $attr);
    $row_opts['label'] = titleize($attr);
    $this->append_class_name($row_opts, 'file');

    $name = $this->form_field_name($obj, $attr);
    $field = $this->file_field_tag($name, $opts);

    return $this->form_row($field, $row_opts);
  }

  public function checkbox_tag($name, $checked = false, $opts = array()) {
    $opts['type'] = 'checkbox';
    $opts['name'] = $name;
    $opts['value'] = isset($opts['value']) ? $opts['value'] : '1';
    $opts['checked'] = $checked ? 'checked' : null;
    $this->append_class_name($opts, 'checkbox');
    $this->append_default_form_field_id($opts, $name);
    return $this->tag('input', null, $opts);
  }

  public function checkbox($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $hidden = $this->tag('input', null, array(
      'type' => 'hidden',
      'name' => $name,
      'value' => '0',
      'class' => 'hidden',
    ));
    $checkbox = $this->checkbox_tag($name, $obj->$attr, $opts); 
    return $hidden . $checkbox;
  }

  public function checkbox_row($obj, $attr, $opts = array()) {
    $row_opts = $this->form_row_opts($opts);
    $row_opts['error'] = $this->errors_on($obj, $attr);
    $row_opts['label'] = titleize($attr);
    $this->append_class_name($row_opts, 'checkbox');
    return $this->form_row($this->checkbox($obj, $attr, $opts), $row_opts);
  }

  public function text_area_tag($name, $value = null, $opts = array()) {
    $opts['name'] = $name;
    if(!isset($opts['rows'])) $opts['rows'] = 5;
    if(!isset($opts['cols'])) $opts['cols'] = 50;
    $this->append_default_form_field_id($opts, $name);
    return $this->tag('textarea', $value, $opts);
  }

  public function text_area($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $value = $obj->attribute_before_type_cast($attr);
    return $this->text_area_tag($name, $value, $opts);
  }

  public function text_area_row($obj, $attr, $opts = array()) {

    $row_opts = $this->form_row_opts($opts);
    $row_opts['error'] = $this->errors_on($obj, $attr);
    $row_opts['label'] = titleize($attr);
    $this->append_class_name($row_opts, 'textarea');

    $field = $this->text_area($obj, $attr, $opts);

    return $this->form_row($field, $row_opts);

  }

  public function text_field_tag($name, $value = null, $opts = array()) {
    $opts['type'] = 'text';
    $opts['name'] = $name;
    $opts['value'] = $value;
    $this->append_class_name($opts, 'text');
    $this->append_default_form_field_id($opts, $name);
    return $this->tag('input', null, $opts);
  }

  public function text_field($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $value = $obj->attribute_before_type_cast($attr);
    return $this->text_field_tag($name, $value, $opts);
  }

  public function text_field_row($obj, $attr, $opts = array()) {

    $row_opts = $this->form_row_opts($opts);
    $row_opts['error'] = $this->errors_on($obj, $attr);
    $row_opts['label'] = titleize($attr);
    $this->append_class_name($row_opts, 'text');

    $field = $this->text_field($obj, $attr, $opts);

    return $this->form_row($field, $row_opts);

  }

  public function password_field_tag($name, $value = null, $opts = array()) {
    $opts['type'] = 'password';
    $opts['name'] = $name;
    $opts['value'] = $value;
    $this->append_class_name($opts, 'password');
    $this->append_default_form_field_id($opts, $name);
    return $this->tag('input', null, $opts);
  }

  public function password_field($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $value = $obj->attribute_before_type_cast($attr);
    return $this->password_field_tag($name, $value, $opts);
  }

  public function password_field_row($obj, $attr, $opts = array()) {

    $row_opts = $this->form_row_opts($opts);
    $row_opts['error'] = $this->errors_on($obj, $attr);
    $row_opts['label'] = titleize($attr);
    $this->append_class_name($row_opts, 'password');

    $name = $this->form_field_name($obj, $attr);
    $value = $obj->attribute_before_type_cast($attr);
    $field = $this->password_field_tag($name, $value, $opts);

    return $this->form_row($field, $row_opts);

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
  public function form_row($content, $opts = array()) {

    $html = array();

    # label
    if(isset($opts['label']) && $opts['label']) {
      $label = $opts['label'];
      if(isset($opts['required']) && $opts['required']) {
        $symbol = $this->tag('span', '*', array('class' => 'required_symbol'));
        $label = "$symbol $label";
      }
      $for = isset($opts['label_for']) ? $opts['label_for'] : null;
      $html[] = $this->tag('label', $label, array('for' => $for));
    }

    # content
    $html[] = $content;

    # error message
    if(isset($opts['error']) && $opts['error']) {
      $html[] = $this->tag('p', $opts['error'], array('class' => 'error'));
    }

    # hint
    if(isset($opts['hint']) && $opts['hint']) {
      $html[] = $this->tag('p', $opts['hint'], array('class' => 'hint'));
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
    return $this->tag('div', $html, array('class' => $css));

  }

  # private
  public function form_row_opts(&$opts) {
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

  protected function errors_on($obj, $attr) {
    $errors = $obj->errors->on($attr);
    return empty($errors) ? null : implode(', ', $errors);
  }

  protected function form_field_id($form_field_name) {
    return preg_replace('/\[(.*)\]/', '_$1', $form_field_name);
  }

  protected function form_field_name($obj, $attr) {
    return strtolower(get_class($obj) . "[$attr]");
  }

  protected function append_default_form_field_id(&$opts, $form_field_name) {
    if(!array_key_exists('id', $opts) && $form_field_name)
      $opts['id'] = $this->form_field_id($form_field_name);
  }

}
