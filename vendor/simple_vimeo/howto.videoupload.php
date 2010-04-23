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

// Lets check if you submitted a file
if(isset($_FILES['uploadfile']['tmp_name'])) {
    // Handle default errors, just example if you fail to set php settings correctly
    if($_FILES['uploadfile']['error'] > 0) {
        echo 'Error handling file upload. Error code: ' . $_FILES['uploadfile']['error'] . '. Check http://php.net/manual/en/features.file-upload.errors.php for details on this code.';
        exit;
    }
    
    // Now lets do the upload
    $oResponse = VimeoVideosRequest::doUpload($_FILES['uploadfile']['tmp_name']);
    
    // Lets get the video ID     
    echo '<p>';
    echo 'Ticket ID: ' . $oResponse->getTicket() . '<br>';
    echo 'Video ID: ' . $oResponse->getVideoID() . '<br>';
    echo 'Uploading: ' . ($oResponse->isUploading() ? 'yes' : 'no') . '<br>';
    echo 'Transcoding: '  . ($oResponse->isTranscoding() ? 'yes' : 'no') . '<br>';
    echo 'Transcoding progress: ' . $oResponse->getTranscodingProgress();
    echo '</p>';
    
    $iVideoID = $oResponse->getVideoID();
    
    // Now lets update the video to be more detailed
    // Set title
    VimeoVideosRequest::setTitle($iVideoID, 'api-upload-test' . mt_rand(10000,99999));
    
    // Set caption
    VimeoVideosRequest::setCaption($iVideoID, 'This video was uploaded using php5-simplevimeo, please delete if testing is done');
    
    // Set it favorite
    VimeoVideosRequest::setFavorite($iVideoID, true);
    
    // Add some tags, you can pass "some, tags" like this or an php array
    VimeoVideosRequest::addTags($iVideoID, array('php5', 'simplevimeo'));
    
    // Clear tags, but we dont want to right now, we just set them
    // VimeoVideosRequest::clearTags($iVideoID);
    
    // Make the video completly private
    VimeoVideosRequest::setPrivacy($iVideoID, VimeoVideosRequest::PRIVACY_NOBODY);
} else {
    echo 'Error handling file upload: Uploaded file was not set?!';
}

include('stylestuff/footer.php');
?>