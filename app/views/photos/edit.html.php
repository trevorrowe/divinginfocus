<?php echo $this->title('Edit Photo') ?>
<form method='post' action='<?php echo url('update', $photo) ?>'>
  <?php echo $this->text_field_row($photo, 'title') ?>
  <?php echo $this->text_area_row($photo, 'caption') ?>
  <?php echo $this->submit_row('Save') ?>
</form>
