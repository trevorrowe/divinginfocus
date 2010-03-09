$('input[type=checkbox].toggle').click(function() {
  var $checkbox = $(this);
  var $spinner = $("<img src='/images/admin/ajax_toggle_spinner.gif' />");
  $checkbox.hide();
  $checkbox.after($spinner);
  $.ajax({
    url      : $checkbox.attr('data-url'),
    success  : function() { $checkbox[0].checked = !$checkbox[0].checked; },
    error    : function() { alert('Sorry, something went wrong.'); },
    complete : function() { $checkbox.show(); $spinner.remove(); }
  });
  return false;
});
