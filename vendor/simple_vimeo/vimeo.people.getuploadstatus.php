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

// Add my api test account
$oResponse = VimeoPeopleRequest::getUploadStatus();

echo '<p>';
echo 'Max: ' . $oResponse->getMaxBytes() . ' bytes / ' . $oResponse->getMaxKiloBytes() . ' kbytes<br>';
echo 'Used: ' . $oResponse->getUsedBytes() . ' bytes / ' . $oResponse->getUsedKiloBytes() . ' kbytes<br>';
echo 'Remaining: ' . $oResponse->getRemainingBytes() . ' bytes / ' . $oResponse->getRemainingKiloBytes() . ' kbytes<br>';
echo '</p>';

include('stylestuff/footer.php');
?>