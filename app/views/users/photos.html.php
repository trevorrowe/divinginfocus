<?php echo $this->title($user->username . '\'s Photos') ?>
<?php echo $this->paginate($photos) ?>
<?php echo $this->quilt($photos) ?>
