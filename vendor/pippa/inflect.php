<?php

namespace Pippa;

# camelize
# capitalize
# classify
# dasherize
# foreign_key
# humanize
# ordinalize
# pluralize
# singularize
# tableize
# titleize
# underscore

class Inflect {

  public static $singular = array(
    array('/(quiz)zes$/i', '\1'), 
    array('/(matr)ices$/i', '\1ix'), 
    array('/(vert|ind)ices$/i', '\1ex'), 
    array('/^(ox)en/i', '\1'), 
    array('/(alias|status)es$/i', '\1'), 
    array('/([octop|vir])i$/i', '\1us'), 
    array('/(cris|ax|test)es$/i', '\1is'), 
    array('/(shoe)s$/i', '\1'), 
    array('/(o)es$/i', '\1'), 
    array('/(bus)es$/i', '\1'), 
    array('/([m|l])ice$/i', '\1ouse'), 
    array('/(x|ch|ss|sh)es$/i', '\1'), 
    array('/(m)ovies$/i', '\1ovie'), 
    array('/(s)eries$/i', '\1eries'), 
    array('/([^aeiouy]|qu)ies$/i', '\1y'), 
    array('/([lr])ves$/i', '\1f'), 
    array('/(tive)s$/i', '\1'), 
    array('/(hive)s$/i', '\1'), 
    array('/([^f])ves$/i', '\1fe'), 
    array('/(^analy)ses$/i', '\1sis'), 
    array('/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i', '\1\2sis'), 
    array('/([ti])a$/i', '\1um'), 
    array('/(n)ews$/i', '\1ews'), 
    array('/s$/i', ''),
  );

  public static $plural = array(
    array('/(quiz)$/i', '\1zes'),
    array('/^(ox)$/i', '\1en'),
    array('/([m|l])ouse$/i', '\1ice'),
    array('/(matr|vert|ind)ix|ex$/i', '\1ices'),
    array('/(x|ch|ss|sh)$/i', '\1es'),
    array('/([^aeiouy]|qu)ies$/i', '\1y'),
    array('/([^aeiouy]|qu)y$/i', '\1ies'),
    array('/(hive)$/i', '\1s'),
    array('/(?:([^f])fe|([lr])f)$/i', '\1\2ves'),
    array('/sis$/i', 'ses'),
    array('/([ti])um$/i', '\1a'),
    array('/(buffal|tomat)o$/i', '\1oes'),
    array('/(bu)s$/i', '\1ses'),
    array('/(alias|status)$/i', '\1es'),
    array('/(octop|vir)us$/i', '\1i'),
    array('/(ax|test)is$/i', '\1es'),
    array('/s$/i', 's'),
    array('/$/', 's'),
  );

  public static $uncountable = array(
    'equipment', 'fish', 'information', 'money', 'rice', 'species', 
    'series', 'sheep',
  );

  public static $human = array();

  public static $cache = array(
    'singular' => array(),
    'plural' => array(),
  );

  public static function singular($regex, $replace) {
    array_unshift(self::$plural, array($regex, $replace));
  }

  public static function plural($regex, $replace) {
    array_unshift(self::$singular, array($regex, $replace));
  }

  public static function irregular($singluar, $plural) {
    self::plural('/('.preg_quote(substr($singular,0,1)).')'.preg_quote(substr($singular,1)).'$/i', '\1'.preg_quote(substr($plural,1)));
    self::singular('/('.preg_quote(substr($plural,0,1)).')'.preg_quote(substr($plural,1)).'$/i', '\1'.preg_quote(substr($singular,1)));
  }

  public static function uncountable($word) {
    self::$uncountable[] = $word;
  }

  public static function human($regex, $replace) {
    array_unshift(self::$human, array($regex, $replace));
  }

}
