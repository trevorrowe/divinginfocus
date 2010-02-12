#!/usr/bin/php
<?php

/*  PEL: PHP Exif Library.  A library with support for reading and
 *  writing all Exif headers in JPEG and TIFF images using PHP.
 *
 *  Copyright (C) 2004, 2005, 2006  Martin Geisler.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program in the file COPYING; if not, write to the
 *  Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 *  Boston, MA 02110-1301 USA
 */

/* $Id: run-tests.php 431 2006-09-05 22:06:23Z mgeisler $ */

error_reporting(E_ALL);

/* You can define the path to SimpleTest here, or you can let PEL
 * search for it by walking up the directory tree. */
//define('SIMPLE_TEST', '../simpletest/');

if (!defined('SIMPLE_TEST')) {
  /* Search for a directory named 'simpletest' upwards in the
   * directory tree. */
  $dir = 'simpletest/';
  while (!is_file($dir . 'unit_tester.php')) {
    print "Looking for SimpleTest in $dir...\n";
    $dir = '../' . $dir;
  }

  define('SIMPLE_TEST', $dir);
}

if (is_dir(SIMPLE_TEST)) {
  printf("Found SimpleTest version %s in %s!\n",
         file_get_contents(SIMPLE_TEST . 'VERSION'),
         SIMPLE_TEST);
} else {
  print "SimpleTest could not be found and so no tests can be made.\n";
  print "Please download SimpleTest from http://simpletest.sf.net/\n";
  print "and unpack it in as one of the directories mentioned above.\n";
  exit(1);
}

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');

if ($argc > 1) { 
  /* If command line arguments are given, then only test those. */
  array_shift($argv);
  $tests = $argv;
  $group = new GroupTest('Selected PEL tests');
} else {
  /* otherwive test all .php files, except this file (run-tests.php). */
  $tests = array_diff(glob('*.php'), array('run-tests.php'));
  $group = new GroupTest('All PEL tests');

  /* Also test all image tests (if they are available). */
  if (is_dir('image-tests')) {
    $image_tests = array_diff(glob('image-tests/*.php'),
                              array('image-tests/make-image-test.php'));
    $image_group = new GroupTest('Image Tests');
    foreach ($image_tests as $image_test)
      $image_group->addTestFile($image_test);
    
    $group->addTestCase($image_group);
  } else {
    echo "Found no image tests, only core functionality will be tested.\n";
    echo "Image tests are available from http://pel.sourceforge.net/.\n";
  }

}

foreach ($tests as $test)
  $group->addTestFile($test);

$group->run(new TextReporter());

?>