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

function displayPermission($sMethod) {
    $sName = '';
    switch(VimeoMethod::getPermissionRequirementForMethod($sMethod)) {
        case VimeoBase::PERMISSION_NONE:        $sName = 'None';        break;
        case VimeoBase::PERMISSION_READ:        $sName = 'Read';        break;
        case VimeoBase::PERMISSION_WRITE:       $sName = 'Write';       break;
        case VimeoBase::PERMISSION_DELETE:      $sName = 'Delete';      break;
    }
    
    return 'Required permission: ' . $sName;
}

include('stylestuff/header.php');
?>

<h1>Vimeo API Examples - Main Menu</h1>

<!-- vimeo.test.* -->
<h2>vimeo.test.login</h2>
<p>
    <?php echo displayPermission('vimeo.test.login'); ?><br>
    <a href="vimeo.test.login.php">Visit example page</a>
</p>

<h2>vimeo.test.echo</h2>
<p>
    <?php echo displayPermission('vimeo.test.echo'); ?><br>
    <a href="vimeo.test.echo.php">Visit example page</a>
</p>

<h2>vimeo.test.null</h2>
<p>
    <?php echo displayPermission('vimeo.test.null'); ?><br>
    <a href="vimeo.test.null.php">Visit example page</a>
</p>

<!-- vimeo.videos.* -->
<h2>vimeo.videos.getList</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getList'); ?><br>
    <form name="search" method="post" action="vimeo.videos.getlist.php">
    <input type="text" name="q" value="151542"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.getUploadedList</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getUploadedList'); ?><br>
    <form name="search" method="post" action="vimeo.videos.getuploadedlist.php">
    <input type="text" name="q" value="151542"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.getAppearsInList</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getAppearsInList'); ?><br>
    <form name="search" method="post" action="vimeo.videos.getappearsinlist.php">
    <input type="text" name="q" value="151542"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.getSubscriptionsList</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getSubscriptionsList'); ?><br>
    <form name="search" method="post" action="vimeo.videos.getsubscriptionslist.php">
    <input type="text" name="q" value="151542"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.getListByTag</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getListByTag'); ?><br>
    <form name="search" method="post" action="vimeo.videos.getlistbytag.php">
    <input type="text" name="q" value="summer"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.getLikeList</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getLikeList'); ?><br>
    <form name="search" method="post" action="vimeo.videos.getlikelist.php">
    <input type="text" name="q" value="151542"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.getContactsList</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getContactsList'); ?><br>
    <form name="search" method="post" action="vimeo.videos.getcontactslist.php">
    <input type="text" name="q" value="151542"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.getContactsLikeList</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getContactsLikeList'); ?><br>
    <form name="search" method="post" action="vimeo.videos.getcontactslikelist.php">
    <input type="text" name="q" value="151542"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.search</h2>
<p>
    <?php echo displayPermission('vimeo.videos.search'); ?><br>
    <form name="search" method="post" action="vimeo.videos.search.php">
    <input type="text" name="q"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.getInfo</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getInfo'); ?><br>
    <form name="search" method="post" action="vimeo.videos.getinfo.php">
    <input type="text" name="q" value="285264"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.videos.getUploadTicket</h2>
<p>
    <?php echo displayPermission('vimeo.videos.getUploadTicket'); ?><br>
    <a href="vimeo.videos.getuploadticket.php">Get upload ticket</a>
</p>

<h2>vimeo.videos.checkUploadStatus</h2>
<p>
    <?php echo displayPermission('vimeo.videos.checkUploadStatus'); ?><br>
    <form name="search" method="post" action="vimeo.videos.checkuploadstatus.php">
    <input type="text" name="q" value=""><input type="submit" value="Check upload status">
    </form>
</p>

<h2>HOWTO: Upload video</h2>
<p>
    <?php echo displayPermission('vimeo.videos.checkUploadStatus'); ?><br>
    <form name="search" method="post" action="howto.videoupload.php" enctype="multipart/form-data">
    <input type="file" name="uploadfile"><input type="submit" value="Upload video">
    </form>
</p>

<h2>HOWTO: Delete video</h2>
<p>
    <?php echo displayPermission('vimeo.videos.delete'); ?><br>
    <form name="search" method="post" action="vimeo.videos.delete.php">
    <input type="text" name="q"><input type="submit" value="Delete Video ID">
    </form>
</p>

<!-- vimeo.people.* -->
<h2>vimeo.people.findByUserName</h2>
<p>
    <?php echo displayPermission('vimeo.people.findByUserName'); ?><br>
    <form name="search" method="post" action="vimeo.people.findbyusername.php">
    <input type="text" name="q" value="ted"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.people.findByEmail</h2>
<p>
    <?php echo displayPermission('vimeo.people.findByEmail'); ?><br>
    <form name="search" method="post" action="vimeo.people.findbyemail.php">
    <input type="text" name="q" value="adrian@periocode.de"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.people.getInfo</h2>
<p>
    <?php echo displayPermission('vimeo.people.getInfo'); ?><br>
    <form name="search" method="post" action="vimeo.people.getinfo.php">
    <input type="text" name="q" value="151542"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.people.getPortraitUrl</h2>
<p>
    <?php echo displayPermission('vimeo.people.getPortraitUrl'); ?><br>
    <form name="search" method="post" action="vimeo.people.getportraiturl.php">
    <input type="text" name="q" value="ted"><input type="submit" value="Search">
    </form>
</p>

<h2>vimeo.people.addContact</h2>
<p>
    <?php echo displayPermission('vimeo.people.addContact'); ?><br>
    <form name="search" method="post" action="vimeo.people.addcontact.php">
    <input type="text" name="q" value="577454"><input type="submit" value="Add contact">
    </form>
</p>

<h2>vimeo.people.removeContact</h2>
<p>
    <?php echo displayPermission('vimeo.people.removeContact'); ?><br>
    <form name="search" method="post" action="vimeo.people.removecontact.php">
    <input type="text" name="q" value="577454"><input type="submit" value="Remove contact">
    </form>
</p>

<h2>vimeo.people.getUploadStatus</h2>
<p>
    <?php echo displayPermission('vimeo.people.getUploadStatus'); ?><br>
    <a href="vimeo.people.getuploadstatus.php">Show upload status</a>
</p>

<h2>vimeo.people.addSubscription</h2>
<p>
    <?php echo displayPermission('vimeo.people.addSubscription'); ?><br>
    <form name="search" method="post" action="vimeo.people.addsubscription.php">
    <input type="text" name="q" value="577454"><input type="submit" value="Subscribe">
    </form>
</p>

<h2>vimeo.people.removeSubscription</h2>
<p>
    <?php echo displayPermission('vimeo.people.removeSubscription'); ?><br>
    <form name="search" method="post" action="vimeo.people.removesubscription.php">
    <input type="text" name="q" value="577454"><input type="submit" value="Unsubscribe">
    </form>
</p>

<?php include('stylestuff/footer.php'); ?>