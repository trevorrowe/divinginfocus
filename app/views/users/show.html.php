<?php echo $this->title($user->username) ?>

<h2>Photos</h2>
<?php echo $this->link_to('View More &raquo;', $this->user_path($user, array('action' => 'photos'))) ?>

<h2>Videos</h2>

<h2>Albums</h2>
