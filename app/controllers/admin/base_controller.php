<?php

class Admin_BaseController extends ApplicationController {

  public function init() {
    $this->layout('admin');
    $this->before_filter('require_authentication');
    $this->before_filter('require_admin');
  }

  ##
  ## filters
  ##

  public function require_authentication_filter() {
    if(!$this->logged_in()) {
      $warning = 'You must login to view the requested page.';
      $this->flash('warn', $warning);
      $this->redirect('/login');
    }
  }

  public function require_admin_filter() {
    if(!$this->current_user()->admin)
      $this->render_error_page(403);
  }

}
