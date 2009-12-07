<?php

class ApplicationController extends \Pippa\Controller {

  flash[:error] = 'asdf'
  flash('error', 'asdf');

  flash.now[:notice] = 'asdf'
  flash_now('notice', 'asdf');

  flash['error']
  flash('error');

}
