<?php echo $this->title('Upload a Photo') ?>
<form enctype='multipart/form-data' action='<?php echo url('create') ?>' method='POST'>
  <?php echo $this->hidden_field_tag('MAX_FILE_SIZE', 10485760) ?>
  <?php echo $this->file_field_row($photo, 'file') ?>
  <?php echo $this->submit_row('Upload') ?>
</form>
