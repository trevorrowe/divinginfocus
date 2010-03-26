<?php

class ApplicationController extends \Pippa\Controller {

  public function init() {
    parent::init();
    $this->before_filter('login_from_cookie', array('unless' => 'logged_in'));
  }

  ##
  ## filters
  ##

  public function require_user_filter() {
    $this->authenticate_or_redirect();
  }

  public function require_admin_filter() {
    if($this->authenticate_or_redirect())
      if(!$this->current_user()->admin)
        $this->render_error_page(403);
  }

  public function require_sudoer_filter() {
    if($this->authenticate_or_redirect())
      if(!$this->user_can_sudo())
        $this->render_error_page(403);
  }

  public function login_from_cookie_filter() {

    # stop here if the user did not present a login cookie
    $cookie = RememberMeCookie::get();
    if(!$cookie->exists()) 
      return;

    # find the matching cookie in our db
    $db_cookie = LoginCookie::matching_token($cookie)->first();

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
    App::$session->clear();
    App::$session->username = $user->username;
    App::$session->timestamp = time();

    if($remember) {

      # $remember is either a boolean or an existing db login cookie
      if(is_object($remember)) {
        $db_cookie = $remember;
      } else {
        $db_cookie = new LoginCookie();
        $db_cookie->username = $user->username;
        $db_cookie->series = uuid();
      }
      $db_cookie->token = uuid();
      $db_cookie->savex();

      $rmc = RememberMeCookie::get();
      $rmc->username = $user->username;
      $rmc->series = $db_cookie->series;
      $rmc->token = $db_cookie->token;
      $rmc->save();

    }

    # create a record of the login
    $login = new Login();
    $login->username = $user->username;
    $login->via = is_object($remember) ? 'cookie' : 'form';
    $login->savex();
  }

  public function logout() {

    # clear the session
    App::$session->clear();

    # get rid of any persistant login cookies
    $cookie = RememberMeCookie::get();
    LoginCookie::matching_series($cookie)->delete_all();
    $cookie->delete();
  }

  protected function authenticate_or_redirect() {
    if(!$this->logged_in()) {
      $warning = 'You must login to view the requested page.';
      $this->flash('warn', $warning);
      $this->redirect('/login');
      return false;
    } 
    return true;
  }

}
