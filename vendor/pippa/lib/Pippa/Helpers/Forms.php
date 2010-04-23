<?php

namespace Pippa\Helpers;

class Forms extends \Pippa\Helper {

  public function form_field_tag($name, $content, $opts) {
    # convert options like disabled from selected => true to forms like:
    # selected => selected, this is because the html element requires the
    # text to be the attribute name & value
    foreach(array('selected', 'disabled') as $key) {
      if(isset($opts[$key]) && $opts[$key])
        $opt[$key] = $key;
    }
    return $this->tag($name, $content, $opts);
  }

  public function submit_tag($label, $opts = array()) {
    $opts['type'] = 'submit';
    $opts['value'] = $label;
    $this->append_class_name($opts, 'submit');
    return $this->form_field_tag('input', null, $opts);
  }

  public function submit_row($label, $opts = array()) {
    $row_opts = $this->form_row_opts($opts, 'submit');
    return $this->form_row($this->submit_tag($label, $opts), $row_opts);
  }

  public function hidden_field_tag($name, $value = null, $opts = array()) {
    $opts['type'] = 'hidden'; 
    $opts['value'] = $value;
    $opts['name'] = $name;
    $this->append_default_form_field_id($opts, $name);
    return $this->form_field_tag('input', null, $opts);
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
    return $this->form_field_tag('input', null, $opts);
  }

  public function file_field($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    return $this->file_field_tag($name, $opts);
  }

  public function file_field_row($obj, $attr, $opts = array()) {

    $row_opts = $this->form_row_opts($opts, 'file', $obj, $attr);

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
    return $this->form_field_tag('input', null, $opts);
  }

  public function checkbox($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $hidden = $this->form_field_tag('input', null, array(
      'type' => 'hidden',
      'name' => $name,
      'value' => '0',
      'class' => 'hidden',
    ));
    $checkbox = $this->checkbox_tag($name, $obj->$attr, $opts); 
    return $hidden . $checkbox;
  }

  public function checkbox_row($obj, $attr, $opts = array()) {
    $row_opts = $this->form_row_opts($opts, 'checkbox', $obj, $attr);
    return $this->form_row($this->checkbox($obj, $attr, $opts), $row_opts);
  }

  public function text_area_tag($name, $value = null, $opts = array()) {
    $opts['name'] = $name;
    if(!isset($opts['rows'])) $opts['rows'] = 5;
    if(!isset($opts['cols'])) $opts['cols'] = 50;
    $this->append_default_form_field_id($opts, $name);
    return $this->form_field_tag('textarea', $value, $opts);
  }

  public function text_area($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $value = $obj->attribute_before_type_cast($attr);
    return $this->text_area_tag($name, $value, $opts);
  }

  public function text_area_row($obj, $attr, $opts = array()) {

    $row_opts = $this->form_row_opts($opts, 'textarea', $obj, $attr);

    $field = $this->text_area($obj, $attr, $opts);

    return $this->form_row($field, $row_opts);

  }

  public function text_field_tag($name, $value = null, $opts = array()) {
    $opts['type'] = 'text';
    $opts['name'] = $name;
    $opts['value'] = $value;
    $this->append_class_name($opts, 'text');
    $this->append_default_form_field_id($opts, $name);
    return $this->form_field_tag('input', null, $opts);
  }

  public function text_field($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $value = $obj->attribute_before_type_cast($attr);
    return $this->text_field_tag($name, $value, $opts);
  }

  public function text_field_row($obj, $attr, $opts = array()) {

    $row_opts = $this->form_row_opts($opts, 'text', $obj, $attr);

    $field = $this->text_field($obj, $attr, $opts);

    return $this->form_row($field, $row_opts);

  }

  public function password_field_tag($name, $value = null, $opts = array()) {
    $opts['type'] = 'password';
    $opts['name'] = $name;
    $opts['value'] = $value;
    $this->append_class_name($opts, 'password');
    $this->append_default_form_field_id($opts, $name);
    return $this->form_field_tag('input', null, $opts);
  }

  public function password_field($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $value = $obj->attribute_before_type_cast($attr);
    return $this->password_field_tag($name, $value, $opts);
  }

  public function password_field_row($obj, $attr, $opts = array()) {

    $row_opts = $this->form_row_opts($opts, 'password', $obj, $attr);

    $name = $this->form_field_name($obj, $attr);
    $value = $obj->attribute_before_type_cast($attr);
    $field = $this->password_field_tag($name, $value, $opts);

    return $this->form_row($field, $row_opts);

  }

  public function options_for_select($values, $selected = null) {

    if(!is_array($values)) {
      $err = "options_for_select requires an array as the first argument";
      throw new Exception($err);
    } 

    $value_pairs = array();
    if(is_assoc($values)) {
      foreach($values as $text => $value)
        $value_pairs[] = array($text, $value);
    } else {
      $value_pairs = $values;
    }

    $selected = is_array($selected) ? $selected : array($selected);

    foreach($value_pairs as $pair) {
      $text = $pair[0];
      $value = $pair[1];
      $sel = in_array($value, $selected) ?  " selected='selected'" : '';
      $tags[] = "<option value='$value'{$sel}>$text</option>";
    }

    return implode("\n", $tags);
  }

  # multipe => true/false
  # disabled => true/false
  public function select_tag($name, $option_tags, $opts = array()) {
    $opts['name'] = $name;
    return $this->form_field_tag('select', $option_tags, $opts);
  }

  public function date_select_tag($name, $datetime = null, $opts = array()) {

    $this_year  = (int) date('Y');
    $this_month = (int) date('m');
    $this_day   = (int) date('d');

    if($datetime) {
      $timestamp = $datetime->getTimestamp();
      $selected_year = (int) strftime('%Y', $timestamp);
      $selected_month = (int) strftime('%m', $timestamp);
      $selected_day = (int) strftime('%d', $timestamp);
    } else {
      $selected_year = $this_year;
      $selected_month = $this_month;
      $selected_day = $this_day;
    }

    $year_opts = "<option value=''></option>";
    $start_year = $this_year - 5;
    $end_year = $this_year + 5;
    for($year = $start_year; $year <= $end_year; ++$year) {
      $sel = $year == $selected_year ? " selected='selected'" : '';
      $year_opts .= "<option value='$year'{$sel}>$year</option>";
    }

    $months = array('', 
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    );

    $month_opts = "<option value=''></option>";
    for($month = 1; $month <= 12; ++$month) {
      $sel = $month == $selected_month ? " selected='selected'" : '';
      $month_opts .= "<option value='$month'{$sel}>{$months[$month]}</option>";
    }

    $day_opts = "<option value=''></option>";
    for($day = 1; $day <= 31; ++$day) {
      $sel = $day == $selected_day ? " selected='selected'" : '';
      $day_opts .= "<option value='$day'{$sel}>$day</option>";
    }

    $year = $this->select_tag("{$name}[year]", $year_opts);
    $month = $this->select_tag("{$name}[month]", $month_opts);
    $day = $this->select_tag("{$name}[day]", $day_opts);
    return "$year - $month - $day";
  }

   # Options ideas:
   # 
   # * use_month_numbers - Set to true if you want to use month numbers 
   #   rather than month names (e.g. 2" instead of "February").
   # * use_short_month - Set to true if you want to use the abbreviated month 
   #   name instead of the full name (e.g. "Feb" instead of "February").
   # * add_month_number - Set to true if you want to show both, the month‘s 
   #   number and name (e.g. "2 - February" instead of "February").
   #   :use_month_names - Set to an array with 12 month names if you want to 
   #   customize month names. Note: You can also use Rails’ new i18n 
   #   functionality for this.
   # * date_separator - Specifies a string to separate the date fields. 
   #   Default is "" (i.e. nothing).
   # * start_year - Set the start year for the year select. Default is 
   #   Time.now.year - 5.
   # * end_year - Set the end year for the year select. Default is 
   #   Time.now.year + 5.
   # * discard_day - Set to true if you don‘t want to show a day select. This 
   #   includes the day as a hidden field instead of showing a select field. 
   #   Also note that this implicitly sets the day to be the first of the 
   #   given month in order to not create invalid dates like 31 February.
   # * discard_month - Set to true if you don‘t want to show a month select. 
   #   This includes the month as a hidden field instead of showing a select 
   #   field. Also note that this implicitly sets :discard_day to true.
   # * discard_year - Set to true if you don‘t want to show a year select. 
   #   This includes the year as a hidden field instead of showing a select 
   #   field.
   # * order - Set to an array containing :day, :month and :year do customize 
   #   the order in which the select fields are shown. If you leave out any 
   #   of the symbols, the respective select will not be shown (like when you 
   #   set :discard_xxx => true. Defaults to the order defined in the 
   #   respective locale (e.g. [:year, :month, :day] in the en locale that 
   #   ships with Rails).
   # * include_blank - Include a blank option in every select field so it‘s 
   #   possible to set empty dates.
   # * default - Set a default date if the affected date isn‘t set or is nil.
   # * disabled - Set to true if you want show the select fields as disabled.
   #
  public function datetime_select_tag($name, $datetime=null, $opts = array()) {

    $this_hour = (int) date('H');
    $this_min =  (int) date('H');
    $this_sec =  (int) date('H');

    if($datetime) {
      $timestamp = $datetime->getTimestamp();
      $selected_hour = (int) strftime('%H', $timestamp);
      $selected_min = (int) strftime('%M', $timestamp);
      $selected_sec = (int) strftime('%S', $timestamp);
    } else {
      $selected_hour = $this_year;
      $selected_min = $this_min;
      $selected_sec = $this_sec;
    }

    $hour_opts = "<option value=''></option>";
    for($hour = 0; $hour < 24; ++$hour) {
      $sel = $hour == $selected_hour ? " selected='selected'" : '';
      $hour_opts .= "<option value='$hour'{$sel}>$hour</option>";
    }

    $min_opts = "<option value=''></option>";
    for($min = 0; $min < 60; ++$min) {
      $sel = $min == $selected_min ? " selected='selected'" : '';
      $min_opts .= "<option value='$min'{$sel}>$min</option>";
    }

    $sec_opts = "<option value=''></option>";
    for($sec = 0; $sec < 60; ++$sec) {
      $sel = $sec == $selected_sec ? " selected='selected'" : '';
      $sec_opts .= "<option value='$sec'{$sel}>$sec</option>";
    }

    $date = $this->date_select_tag($name, $datetime, $opts);
    $hour = $this->select_tag("{$name}[hour]", $hour_opts);
    $min = $this->select_tag("{$name}[minute]", $min_opts);
    $sec = $this->select_tag("{$name}[second]", $sec_opts);
    return "$date &nbsp; $hour : $min : $sec";
  }

  public function datetime_select($obj, $attr, $opts = array()) {
    $name = $this->form_field_name($obj, $attr);
    $datetime = $obj->attribute($attr);
    return $this->datetime_select_tag($name, $datetime, $opts);
  }

  public function datetime_select_row($obj, $attr, $opts = array()) {
    $row_opts = $this->form_row_opts($opts, 'datetime', $obj, $attr);
    $field = $this->datetime_select($obj, $attr, $opts);
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

  # Removes certain options from the passed options hash and puts them 
  # into a different hash.  The passed hash is modified (keys are removed)
  # and the new hash is returned.
  public function form_row_opts(&$opts, $class = null, $obj = null, $attr = null) {

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

    if($class)
      $this->append_class_name($row_opts, 'file');

    if($obj && $attr) {
      if(!isset($row_opts['label']))
        $row_opts['label'] = titleize($attr);
      if(!isset($row_opts['error']))
        $row_opts['error'] = $this->errors_on($obj, $attr);
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
    return underscore(get_class($obj)) . "[$attr]";
  }

  protected function append_default_form_field_id(&$opts, $form_field_name) {
    if(!array_key_exists('id', $opts) && $form_field_name)
      $opts['id'] = $this->form_field_id($form_field_name);
  }

}
