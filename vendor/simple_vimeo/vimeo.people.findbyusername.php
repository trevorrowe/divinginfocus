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

$oResponse = VimeoPeopleRequest::findByUsername($_REQUEST['q']);
$oUser = $oResponse->getUser();

echo '<p>';
echo 'ID: ' . $oUser->getID() . '<br>';
echo 'Username: ' . $oUser->getUsername() . '<br>';
echo 'Fullname: ' . $oUser->getFullname() . '<br>';

// Display optional
if($oUser->getNumberOfLikes()) {
    echo 'Location: ' . $oUser->getLocation() . '<br>';
    echo 'URL: ' . $oUser->getUrl() . '<br>';
    echo '# of contacts: ' . $oUser->getNumberOfContacts() . '<br>';
    echo '# of uploads: ' . $oUser->getNumberOfUploads() . '<br>';
    echo '# of likes: ' . $oUser->getNumberOfLikes() . '<br>';
    echo '# of videos: ' . $oUser->getNumberOfVideos() . '<br>';
    echo '# of videos appears in: ' . $oUser->getNumberOfVideosAppearsIn() . '<br>';
    echo 'Profile Url: ' . $oUser->getProfileUrl() . '<br>';
    echo 'Videos Url: ' . $oUser->getVideosUrl() . '<br>';
}

include('stylestuff/footer.php');
?>