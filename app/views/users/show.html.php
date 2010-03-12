<?php echo $this->title($user->username) ?>

<h2>Photos</h2>
<?php echo $this->photo_quilt($photos, url('photos', $user)) ?>

<h2>Videos</h2>

<h2>Albums</h2>
