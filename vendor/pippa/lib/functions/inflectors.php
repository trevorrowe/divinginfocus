<?php

function camelize($lower_case_and_underscored_word) {
  $str = $lower_case_and_underscored_word;
  $str = str_replace('_', ' ', $str);
  $str = ucwords($str);
  $str = str_replace(' ', '', $str);
  $str = str_replace('/', ' ', $str);
  $str = ucwords($str);
  $str = str_replace(' ', '_', $str);
  $str = str_replace('/', '_', $str);
  return $str;
}

function capitalize($word) {
  return ucfirst(strtolower($word));
}

function classify($word) {
  return camelize(singularize($word));
}

function dasherize($word) {
  return str_replace('_', '-', underscore($word));
}

function foreign_key($class_name) {
  return underscore(demodulize($class_name)) . "_id";
}

function humanize($word) {
  if(count(\Pippa\Inflect::$human) > 0) {
    $original = $word;   
    foreach(\Pippa\Inflect::$human as $rule) {
      list($regex, $replace) = $rule;
      $str = preg_replace($regex, $replace, $word);
      if($original != $str) break;
    }	
  }
  return capitalize(str_replace(array('_','_id'), array(' ',''), $word));
}

function ordinalize($num) {
  $num = intval($num);
  if(in_array(($num % 100), range(11, 13))) {
    $num .= 'th';
  } else {
    switch(($num % 10)) {
      case 1:
        $num .= 'st';
        break;
      case 2:
        $num .= 'nd';
        break;
      case 3:
        $num .= 'rd';
        break;
      default:
        $num .= 'th';
    }    
  }
  return $num;
}

function pluralize($singular, $count = 0, $plural = null) {

  if($count == 1)
    return $singular;

  if(!is_null($plural))
    return $plural;

  if(isset(\Pippa\Inflect::$cache['plural'][$singular]))
    return \Pippa\Inflect::$cache['plural'][$singular];

  if(in_array($singular, \Pippa\Inflect::$uncountable))
    return $singular;

  foreach(\Pippa\Inflect::$plural as $rule) {
    list($regex, $replace) = $rule;
    if(preg_match($regex, $singular)) {
      $plural = preg_replace($regex, $replace, $singular);
      \Pippa\Inflect::$cache['plural'][$singular] = $plural;
      return $plural;
    }	
  }

  return $singular;
}

function singularize($plural) {

  if(isset(\Pippa\Inflect::$cache['singular'][$plural]))
    return \Pippa\Inflect::$cache['singular'][$plural];

  if(in_array($plural, \Pippa\Inflect::$uncountable))
    return $plural;

  foreach(\Pippa\Inflect::$singular as $rule) {
    list($regex, $replace) = $rule;
    if(preg_match($regex, $plural)) {
      $singular = preg_replace($regex, $replace, $plural);
      \Pippa\Inflect::$cache['singular'][$plural] = $singular;
      return $singular;
    }	
  }

  return $plural;
}

function tableize($class_name) {
  return pluralize(underscore($class_name));
}

function titleize($word) {
  return ucwords(humanize(underscore($word)));
}

function underscore($camel_cased_word) {
  $str = $camel_cased_word;
  $str = str_replace('_', '/', $str);
  $str = preg_replace('/([A-Z]+)([A-Z])/', '\1_\2', $str);
  return strtolower(preg_replace('/([a-z\d])([A-Z])/', '\1_\2', $str));
}
