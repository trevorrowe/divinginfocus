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

// If no ticket id was given, lets generate a new one
if(!$_REQUEST['q']) {
    $_REQUEST['q'] = VimeoVideosRequest::getUploadTicket();
}

$oResponse = VimeoVideosRequest::checkUploadStatus($_REQUEST['q']);

echo '<p>';
echo 'Ticket ID: ' . $oResponse->getTicket() . '<br>';
echo 'Video ID: ' . $oResponse->getVideoID() . '<br>';
echo 'Uploading: ' . ($oResponse->isUploading() ? 'yes' : 'no') . '<br>';
echo 'Transcoding: '  . ($oResponse->isTranscoding() ? 'yes' : 'no') . '<br>';
echo 'Transcoding progress: ' . $oResponse->getTranscodingProgress();
echo '</p>';

// echo 'Upload Ticket is: ' . $oResponse->getTicket();

include('stylestuff/footer.php');
?>