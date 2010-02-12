<?php

/*  PEL: PHP Exif Library.  A library with support for reading and
 *  writing all Exif headers in JPEG and TIFF images using PHP.
 *
 *  Copyright (C) 2006  Martin Geisler.
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

/* $Id: ifd.php 427 2006-07-11 15:46:59Z mgeisler $ */


class IFDTestCase extends UnitTestCase {

  function __construct() {
    require_once('../PelIfd.php');
    require_once('../PelTag.php');
    require_once('../PelEntryAscii.php');
    parent::__construct('PEL IFD Tests');
  }

  function testIteratorAggretate() {
    $ifd = new PelIfd(PelIfd::IFD0);
    
    $this->assertEqual(sizeof($ifd->getIterator()), 0);

    $desc = new PelEntryAscii(PelTag::IMAGE_DESCRIPTION, 'Hello?');
    $date = new PelEntryTime(PelTag::DATE_TIME, 12345678);

    $ifd->addEntry($desc);
    $ifd->addEntry($date);

    $this->assertEqual(sizeof($ifd->getIterator()), 2);

    $entries = array();
    foreach ($ifd as $tag => $entry) {
      $entries[$tag] = $entry;
    }

    $this->assertIdentical($entries[PelTag::IMAGE_DESCRIPTION], $desc);
    $this->assertIdentical($entries[PelTag::DATE_TIME], $date);
  }

  function testArrayAccess() {
    $ifd = new PelIfd(PelIfd::IFD0);
    
    $this->assertEqual(sizeof($ifd->getIterator()), 0);

    $desc = new PelEntryAscii(PelTag::IMAGE_DESCRIPTION, 'Hello?');
    $date = new PelEntryTime(PelTag::DATE_TIME, 12345678);

    $ifd[] = $desc;
    $ifd[] = $date;

    $this->assertIdentical($ifd[PelTag::IMAGE_DESCRIPTION], $desc);
    $this->assertIdentical($ifd[PelTag::DATE_TIME], $date);

    unset($ifd[PelTag::DATE_TIME]);
    
    $this->assertFalse(isset($ifd[PelTag::DATE_TIME]));
  }

}

?>