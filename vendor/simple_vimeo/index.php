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

include('stylestuff/header.php');
?>
<p><a href="<?php echo VimeoBase::buildAuthenticationUrl(VimeoBase::PERMISSION_READ); ?>">Authenticate with <strong>READ</strong> permission</a></p>
<p><a href="<?php echo VimeoBase::buildAuthenticationUrl(VimeoBase::PERMISSION_WRITE); ?>">Authenticate with <strong>WRITE</strong> permission</a></p>
<p><a href="<?php echo VimeoBase::buildAuthenticationUrl(VimeoBase::PERMISSION_DELETE); ?>">Authenticate with <strong>DELETE</strong> permission</a></p>
<p><a href="mainmenu.php">Do <strong>not</strong> authenticate with Vimeo</a>
<?php include('stylestuff/footer.php'); ?>