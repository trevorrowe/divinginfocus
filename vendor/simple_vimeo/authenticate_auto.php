<?php
/**
* SimpleVimeo
* 
* API Framework for vimeo.com
* @package      SimpleVimeo
* @author       Adrian Rudnik <adrian@periocode.de>
* @link         http://code.google.com/p/php5-simplevimeo/
* @ignore
*/

/**
* Requires simplevimeo base class
*/
require_once('lib/class.vimeo.php');

// This authentication file should only be used for SITE-MODE
// It will just return the token

$oResponse = VimeoAuthRequest::getToken($_REQUEST['frob']);

echo $oResponse->getToken();

?>
