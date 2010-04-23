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

// !!! Apply token if available, may not do anything BUT read the API documentation
// for the current used function. It may have effects on the results based on privacy
if(isset($_COOKIE['vimeo-token'])) {
    VimeoBase::setToken($_COOKIE['vimeo-token']);
}

// Apply token
if(isset($_COOKIE['vimeo-token'])) {
    VimeoBase::setToken($_COOKIE['vimeo-token']);
}

print_r(VimeoTestRequest::ping());

include('stylestuff/header.php');
?>

<?php include('stylestuff/footer.php'); ?>