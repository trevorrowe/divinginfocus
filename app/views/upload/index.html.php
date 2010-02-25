
<?php echo $this->title('Upload Photos &amp; Videos') ?>
<?php $this->add_js('/yui/yahoo-dom-event.js') ?>
<?php $this->add_js('/yui/element-min.js') ?>
<?php $this->add_js('/yui/uploader-min.js') ?>
<?php $this->add_js('upload') ?>

<div id='add_button'>
  <div id='swf_overlay'></div>
  <div id='add_button_text'>Choose Files</div>
</div>

<div id='upload_button' style='display: none;'>Upload Files</div>

<div id='queue' style='display: none;'>
  <table>
    <thead>
      <tr>
        <th>Filename</th>
        <th>Size</th>
        <th>Progress</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<form enctype='multipart/form-data' action='<?php echo url('form_upload') ?>' method='post'>
  <?php echo $this->hidden_field_tag('MAX_FILE_SIZE', 10485760) ?>
  <?php echo $this->file_field_tag('file') ?>
  <?php echo $this->submit_row('Upload') ?>
</form>
