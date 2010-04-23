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
* CASE:
* You have a site. You have one account that should "represent" this site. I.E. Videos uploaded to your site
* should be posted under the "sites" account to vimeo. Just an example case. So how do we go about it:
* 
* 1) We must ensure the site-account is logged in
* 2) We must ensure the site-account has permission for the wanted methods
* 
* This file will demonstrate how this can be accomplished.
* 
* PLEASE USE: authenticate_auto.php file as "Application Callback URL" in your Vimeo API settings.
* It will just return the TOKEN to CURL. Otherwise the whole process will fail!
* 
* Please note, the file will run, yes, but PLEASE do NOT use it as it is for release. Too much traffic.
* I will put a note where you should optimize stuff for your site.
*/

/**
* First, lets do the login.
* NOTE: You can cache the last request time of your automated PHP-scripts. Just redo an login every 3-4 minutes.
* NOT everytime. You must be logged in to use API calls (otherwise you get a "not logged in"-error). You only
* need to log in, vimeo will then internaly expire it after some time, but until then your saved token will work as
* intended.
*/
$bLoggedIn = VimeoBase::login('YOUR_VIMEO_ACCOUNT_EMAIL', 'YOUR_VIMEO_ACCOUNT_PASSWORD');

/**
* Now lets get the permission
* NOTE: You only need to do this ONCE in a lifetime. permit() returns a VIMEO TOKEN. You NEED to save this in your DB,
* dumpfile or otherwise. You NEVER need to call permit() again, only if you dont have a token anymore (or revoked it on accident)
*/
if($bLoggedIn) {
    // permit will get the frob, then the token and pass it to you as return value
    $sToken = VimeoBase::permit(VimeoBase::PERMISSION_WRITE);
} else {
	throw new VimeoBaseException('Failed to loggin');
}

/**
* USUAL STUFF
* Everything from here on can be used in any script. Once you have the token, everything is a rush.
* Lets say you want to upload a video from your site to vimeo, here is how to:
*/

// We need the token to be used for the api calls
VimeoBase::setToken($sToken);

// Now as usual
$oResponse = VimeoVideosRequest::doUpload('/PATH/TO/SOME/VIDEO');

// Set a title
VimeoVideosRequest::setTitle($oResponse->getVideoID(), 'Test vimeo video');

// Set it invisible for others, just for testing purposes
VimeoVideosRequest::setPrivacy($oResponse->getVideoID(), VimeoVideosRequest::PRIVACY_NOBODY);

// Yay, done!
?>
