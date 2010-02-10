// convert login link to a logout link if logged in 
if($.cookie('username')) {
  var $login_logout = $('#login_logout');
  $login_logout.prepend('Logged in as: ' + $.cookie('username') + ', ');
}
