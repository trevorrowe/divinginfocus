<?php 

class Vimeo {

	const API_REST_URL = 'http://www.vimeo.com/api/rest/v2';
	const API_AUTH_URL = 'http://www.vimeo.com/oauth/authorize';
	const API_ACCESS_TOKEN_URL = 'http://www.vimeo.com/oauth/access_token';
	const API_REQUEST_TOKEN_URL = 'http://www.vimeo.com/oauth/request_token';

	private $_consumer_key = false;
	private $_consumer_secret = false;

	private $_token = false;
	private $_token_secret = false;
	private $_upload_md5s = array();
  
	public function __construct($c_key, $c_secret, $t = null, $t_secret = null) {
		$this->_consumer_key = $c_key;
		$this->_consumer_secret = $c_secret;
		if($t && $t_secret)
			$this->set_token($t, $t_secret);
  }

	/**
	 * Set the OAuth token.
	 * 
	 * @param string $token The OAuth token
	 * @param string $token_secret The OAuth token secret
	 * @param string $type The type of token, either request or access
	 * @param boolean $session_store Store the token in a session variable
	 * @return boolean true
	 */
	public function set_token($t, $t_secret, $type = 'access', $store = true) {
		$this->_token = $t;
		$this->_token_secret = $t_secret;
		if($store) {
			App::$session["{$type}_token"] = $t;
			App::$session["{$type}_token_secret"] = $t_secret;
		}
		return true;
	}

}
