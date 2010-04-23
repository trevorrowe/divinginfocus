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

// Now lets do the user search query. We will get an response object containing everything we need
$oResponse = VimeoVideosRequest::getContactsList($_REQUEST['q']);

// We want the result videos as an array of objects
$aoVideos = $oResponse->getVideos();

// Just for code completion
$oVideo = new VimeoVideoEntity();

// Parse all videos
foreach($aoVideos as $oVideo) {
    echo '<p>';
    echo 'ID: ' . $oVideo->getID() . '<br>';
    echo 'Title: ' . $oVideo->getTitle() . '<br>';
    echo 'Caption: ' . substr($oVideo->getCaption(), 20) . '<br>';
    echo 'URL: ' . $oVideo->getUrl() . '<br>';
    echo 'Upload-Time: ' . date('Y-m-d H:i:s', $oVideo->getUploadTimestamp()) . '<br>';
    echo 'Size: ' . $oVideo->getWidth() . 'x' . $oVideo->getHeight() . '<br>';
    echo 'HD-Video: ' . ($oVideo->isHD() ? 'yes' : 'no') . '<br>';
    echo 'Privacy: ' . $oVideo->getPrivacy() . '<br>';
    echo 'Uploading: ' . ($oVideo->isUploading() ? 'yes' : 'no') . '<br>';
    echo 'Transcoding: ' . ($oVideo->isTranscoding() ? 'yes' : 'no') . '<br>';
    echo 'Owner ID: ' . $oVideo->getOwner()->getID() . '<br>';
    echo 'Owner Username: ' . $oVideo->getOwner()->getUsername() . '<br>';
    echo 'Owner Fullname: ' . $oVideo->getOwner()->getFullname() . '<br>';
    echo '# Likes: ' . $oVideo->getNumberOfLikes() . '<br>';
    echo '# Plays: ' . $oVideo->getNumberOfPlays() . '<br>';
    echo '# Comments: ' . $oVideo->getNumberOfComments() . '<br>';

    // Print all tags
    $aTags = array();
    foreach($oVideo->getTags() as $oTag) {
        $aTags[] = $oTag->getTag();
    }
    echo 'Tags: ' . implode(', ', $aTags) . '<br>';
    echo '</p>';
    echo '<hr>';
}

include('stylestuff/footer.php');
?>