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


include('stylestuff/header.php');

// Check if the user is logged in currently
$bLoggedIn = true;
try {
    VimeoTestRequest::login();
} catch(VimeoTestLoginException $e) {
    switch($e->getCode()) {
        case 99:    $bLoggedIn = false;         break;
        default:    $bLoggedIn = false; exit;   break;  // Some other fatal error occured with api query
    }
}

if($bLoggedIn) {
    echo 'User is logged in';
} else {
    echo 'User is not logged in';
}

include('stylestuff/footer.php'); ?>