<?php echo $this->title($user->username . '\'s Photos') ?>
<?php echo $this->paginate($videos) ?>
<?php echo $this->quilt($videos) ?>
