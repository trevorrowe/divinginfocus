//= require <yui/yahoo-dom-event>
//= require <yui/element>
//= require <yui/uploader>
//= require <jquery>
//= require <templates>

YAHOO.widget.Uploader.SWFURL = '/yui/uploader.swf';

var uploader = new YAHOO.widget.Uploader('swf_overlay'); 

var $upload_button = $('#upload_button');

uploader.addListener('contentReady', contentReadyHandler);
uploader.addListener('fileSelect', fileSelectHandler);
uploader.addListener('mouseDown', mouseDownHandler);
uploader.addListener('mouseUp', mouseUpHandler);
uploader.addListener('rollOut', rollOutHandler);
uploader.addListener('rollOver', rollOverHandler);
uploader.addListener('uploadCancel', uploadCancelHandler);
uploader.addListener('uploadComplete', uploadCompleteHandler);
uploader.addListener('uploadCompleteData', uploadCompleteDataHandler);
uploader.addListener('uploadError', uploadErrorHandler);
uploader.addListener('uploadProgress', uploadProgressHandler);
uploader.addListener('uploadStart', uploadStartHandler);

$upload_button.click(function() {
  var vars = {};
  uploader.uploadAll('/upload/flash_upload.json', 'POST', vars);
});

/**
 * Fires when the SWF file has loaded and is ready to be initialized
 */
function contentReadyHandler() {

  uploader.setAllowMultipleFiles(true);
  uploader.setSimUploadLimit(4);

  // New set of file filters. 
  var file_filters = new Array(
    {description:"Images", extensions:"*.jpg"}, 
    {description:"Videos", extensions:"*.avi"}
  ); 

  // Apply new set of file filters to the uploader. 
  uploader.setFileFilters(file_filters)
  
}

/** 
 * Fires when the user has finished selecting files in the "Open File" dialog.
 * Parameters:
 *   event.type <String> The event type 
 *   event.fileList <Object> A dictionary of objects with file information 
 *   event.fileList[].size <Number> File size in bytes
 *   event.fileList[].cDate <Date> Creation date
 *   event.fileList[].mDate <Date> Modification date
 *   event.fileList[].name <String> File name
 *   event.fileList[].id <String> Unique file id
 */
function fileSelectHandler(event) {

  $queue_table = $('#queue').show();

  var files = {};
  $.each(event.fileList, function() {
    var dupe = find_dupe(files, this);
    if(dupe) {
      delete files[dupe.id];
      uploader.removeFile(dupe.id)
    }
    files[this.id] = this;
  });

  $queue = $queue_table.find('tbody').html('');
  $.each(files, function() {
    // we have to prepend (instead of append) the rows to our visual queue
    // because that is the order YUI uploader will upload them.
    $new_row = $(" <tr> <td class='filename'></td> <td class='filesize'></td> <td><div class='progress'><div class='bar'></div></div></td> </tr>");
    $new_row.attr('id', this.id);
    $new_row.find('td.filename').text(this.name);
    $new_row.find('td.filesize').text(this.size);
    $queue.prepend($new_row);
  });

  $upload_button.show();

}

/**
 * Fires when the mouse is pressed over the Uploader. Only fires when the 
 * Uploader UI is enabled and the render type is 'transparent'.
 * Parameters:
 *   event.type <String> The event type 
 */
function mouseDownHandler(event) {
}

/**
 * Fires when the mouse is released over the Uploader. Only fires when the 
 * Uploader UI is enabled and the render type is 'transparent'.
 * Parameters:
 *   event.type <String> The event type 
 */
function mouseUpHandler(event) {
}

/**
 * Fires when the mouse rolls out of the Uploader.
 * Parameters:
 *   event.type <String> The event type 
 */
function rollOutHandler(event) {
}

/**
 * Fires when the mouse rolls over the Uploader.  
 * Parameters:
 *   event.type <String> The event type 
 */
function rollOverHandler(event) {
}

/**
 * Fires when an upload for a specific file is cancelled.
 * Parameters:
 *   event.type <String> The event type 
 *   event.id <String> The id of canceled file
 */
function uploadCancelHandler(event) {
  //console.log('uploadCancel');
}

/**
 * Fires when an upload for a specific file is complete.
 * Parameters:
 *   event.type <String> The event type 
 *   event.id <String> The id of the completed file
 */
function uploadCompleteHandler(event) {
  //console.log("uploadCompleteHandler: %o", event);
}

/**
 * Fires when the server sends data in response to a completed upload.
 * Parameters:
 *   event.type <String> The event type 
 *   event.id <String> The id of the completed file
 *   event.data <String> The raw data returned by the server
 */
function uploadCompleteDataHandler(event) {
  //console.log("uploadCompleteDataHandler: %o", event);
  setFileProgress(event.id, 100); 
  //console.log('uploadCompleteData: ' + event.data);
  // TODO : if we want to allow uploading more with the same form we need 
  // TODO   to remove the completed (and error'd files) from the uploader
  // TODO   and possibly from the queue?
}

/**
 * Fires when an upload error occurs.
 * Parameters:
 *   event.type <String> The event type 
 *   event.id <String> The id of the file encountered an error during upload
 *   event.status <String> The status message associated with the error 
 */
function uploadErrorHandler(event) {
  //console.log('uploadError: %o' + event);
}

/**
 * Fires when new information about the upload progress for a file is available.
 * Parameters:
 *   event.type <String> The event type 
 *   event.id <String> The id of the file with which is in progress
 *   bytesLoaded <Number> The number of bytes of the file uploaded so far 
 *   bytesTotal <Number> The total size of the file 
 */
function uploadProgressHandler(event) {
  var percent = parseInt(event.bytesLoaded / event.bytesTotal * 100);
  setFileProgress(event.id, percent);
}

/**
 * Fires when an upload of a specific file has started.
 * Parameters:
 *   event.type <String> The event type 
 *   event.id <String> The id of the file that's started to upload 
 */
function uploadStartHandler(event) {
  //console.log('starting: ' + event.id);
}

function setFileProgress(file_id, percent) {
  $('#' + file_id).find('.progress .bar').css('width', percent + '%');
}

/**
 * Given a file object it will return either false (no duplicate found)
 * or another file object.  If the file itself is already in the queue,
 * false is returned (as it is not a duplciate).
 */
function find_dupe(files, file) {
  var dupe = false;
  $.each(files, function() {
    if(this.name == file.name && 
       this.size == file.size &&
       this.cDate.toString() == file.cDate.toString() &&
       this.mDate.toString() == file.mDate.toString())
    {
      dupe = this;
      return true;
    }
  });
  return dupe;
}

