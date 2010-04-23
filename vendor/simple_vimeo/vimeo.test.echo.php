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

// Build up an array with stuff, so we can call echo and see if everything works fine
$aTestArgs = array(
    'a' => 'somewhere',
    'b' => 'over',
    'c' => 'the',
    'd' => 'rainbow'
);

// Now lets call the function
$oResponse = VimeoTestRequest::echoback($aTestArgs);

// And see the result

include('stylestuff/header.php');

echo '<pre>';
print_r($oResponse->getResponseArray());
echo '<pre>';

include('stylestuff/footer.php'); ?>
