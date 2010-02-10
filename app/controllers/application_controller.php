<?php

class ApplicationController extends \Pippa\Controller {

  public function init() {
    parent::init();
    $this->before_filter('login_from_cookie', array('unless' => 'logged_in'));
  }

  ##
  ## filters
  ##

  public function login_from_cookie_filter() {

    # stop here if the user did not present a login cookie
    $cookie = RememberMeCookie::get();
    if(!$cookie->exists()) 
      return;

    # find the matching cookie in our db
    $db_cookie = LoginCookie::matching_token($cookie)->first;

    # a perfect match found, we will log the user in
    if($db_cookie) {
      $this->login($db_cookie->user(), $db_cookie);
      # TODO : redirect the user someplace meaningful
      return;
    }

    # the presented cookie was not found in our db, but we did find others
    # in the same series, we have to assume the worst and consider it
    # session theft
    if(LoginCookie::matching_series($cookie)->count > 0) {
      LoginCookie::matching_user($cookie)->delete_all();
      $cookie->delete();
      $this->flash('error', 'Your session appears to have been hijacked.');
      $this->redirect('/login');
      return;
    }

    # the presented cookie appears to be defunct
    $cookie->delete();

  }

  ##
  ## login to / logout from session
  ##

  public function login($user, $remember) {
    
    # log the user into the session
    \Pippa\App::$session->clear();
    \Pippa\App::$session['user_id'] = $user->id;
    \Pippa\App::$session['timestamp'] = time();

    $cookie = RememberMeCookie::get();
    if($remember) {

      # $remember is either a boolean or an existing db login cookie
      if(is_object($remember)) {
        $db_cookie = $remember;
      } else {
        $db_cookie = new LoginCookie();
        $db_cookie->user_id = $user->id;
        $db_cookie->series = uuid();
      }
      $db_cookie->token = uuid();
      $db_cookie->savex();

      $cookie->user_id = $user->id;
      $cookie->series = $db_cookie->series;
      $cookie->token = $db_cookie->token;
      $cookie->save();

    }

    # save their username in a js accessible cookie
    $domain = '.' . $_SERVER['HTTP_HOST'];
    setcookie('username', $user->username, '0', '/', $domain, false, false);

    # create a record of the login
    $login = new Login();
    $login->user_id = $user->id;
    $login->via = is_object($remember) ? 'cookie' : 'form';
    $login->savex();
  }

  public function logout() {

    # clear the session
    \Pippa\App::$session->clear();

    # get rid of any persistant login cookies
    RememberMeCookie::get()->delete();

    # blank out the username
    $domain = '.' . $_SERVER['HTTP_HOST'];
    setcookie('username', false, 0, '/', $domain, false, false);

  }

}
