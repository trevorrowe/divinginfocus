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

/**
* I presume that the linking was successfull. That means this script saved the token ID as cookie.
* By all means, dont use this technique if you plan on integrating vimeo on your site. Link
* the token with the user in your database.
* 
* But what happend exactly?
* 
* The vimeo login page redirected to something like login.php?frob=abc. You should see something like
* this in your browser url where you had the "Successfull" message.
* 
* We grab the frob and request a valid token with it.
*/

$oResponse = VimeoAuthRequest::getToken($_REQUEST['frob']);

/**
* Now lets save the token in a cookie. The cookie should not be deleted. Otherwise you need to press
* the "login" link again (this time vimeo login will skip the user authentication because it knows:
* we already have it until the user revokes it).
*/

setcookie('vimeo-token', $oResponse->getToken(), time() + 2592000);


/**
* Now lets offer some testing stuff to the user browsing this example
*/

header('Location: mainmenu.php');
?>
