<?php
/**
* SimpleVimeo
* 
* API Framework for vimeo.com
* @package      SimpleVimeo
* @author       Adrian Rudnik <adrian@periocode.de>
* @link         http://code.google.com/p/php5-simplevimeo/
*/

/**
* Enable debug to output raw request and response information
*/

define('VIMEO_DEBUG_REQUEST', true);
define('VIMEO_DEBUG_RESPONSE', true);

/**
* Vimeo base class
*
* Provides vital functions to API (access, permission and object handling)
*
* @package     SimpleVimeo
* @subpackage  Base
*/

class VimeoBase {

    const PROJECT_NAME      = 'php5-simplevimeo';
    
    /**
    * Currently logged in user object
    * @var  VimeoUserEntity
    */
    private static $oUser = false;
    
    /**
    * Currently logged in user permission
    * @var  string
    */
    private static $ePermission = false;
    
    /**
    * Currently logged in user token
    * @var  string
    */
    private static $sToken = false;
    
    /**
    * Vimeo Application API key
    * @var  string
    */
    private static $sApiKey = 'YOUR_API_KEY_HERE';
    
    /**
    * Vimeo Application API secret key
    * @var  string
    */
    private static $sApiSecret = 'YOUR_SECRET_API_KEY_HERE';

    const VIMEO_REST_URL    = 'http://vimeo.com/api/rest/';
    const VIMEO_AUTH_URL    = 'http://vimeo.com/services/auth/';
    const VIMEO_UPLOAD_URL  = 'http://vimeo.com/services/upload/';
    const VIMEO_LOGIN_URL   = 'http://vimeo.com/log_in';

    /**
    * You can choose between the following engines:
    * executeRemoteCall_FSOCK = PHP5 file_get_content and stream_contexts (bad error handling)
    * executeRemoteCall_CURL = CURL is used for file transfer (better error handling)
    */
    const REQUEST_ENGINE_CURL           = 'executeRemoteCall_CURL';
    const VIDEOPOST_ENGINE_FSOCK        = 'executeVideopostCall_CURL';
    
    const PERMISSION_NONE               = false;
    const PERMISSION_READ               = 'read';
    const PERMISSION_WRITE              = 'write';
    const PERMISSION_DELETE             = 'delete';
    
    const COOKIE_FILE                   = '/tmp/simplevimeo.cookies';
    
    const DEBUG_ENABLE					= false;
    const DEBUG_LOGFILE					= '/tmp/simplevimeo.debug';
    
    /**
    * Debug output function
    */
    public static function debug($sTitle, $sContent) {
    	if(self::DEBUG_ENABLE) {
			$sMessage = 'DEBUG ' . date('Y-m-d H:i:s', time()) . "\n";
			$sMessage .= 'CONTENT: ' . $sContent . "\n";
			$sMesasge .= $sContent . "\n\n";
			
			$fhLog = fopen(self::DEBUG_LOGFILE, 'a+');
			
			if(!$fhLog) {
				throw new VimeoBaseException('Debug Logfile "' . self::DEBUG_LOGFILE . '" could not be found or written');
			} else {
				fputs($fhLog, $sMessage);
				fclose($fhLog);
			}
    	}
    }
    
    /**
    * Update Authentication
    * 
    * Initializes user and permission information if a token is present.
    * You can alter this method or skip it if you store user information
    * and permission in an external database. Then i would recommend a
    * VimeoAuthRequest::checkLoogin for confirmation.
    * 
    * @access   private
    * @return   void
    */
    private function updateAuthentication() {
        if(self::$sToken && (!self::$ePermission || !self::$oUser)) {
            $oResponse = VimeoAuthRequest::checkToken(self::$sToken);
            
            // Parse user
            self::$oUser = $oResponse->getUser();
            
            // Parse permission
            self::$ePermission = $oResponse->getPermission();
        }
    }
    
    /**
    * Check permission
    * 
    * Checks the current user permission with the given one. This will be
    * heavily used by the executeRemoteCall method to ensure the user
    * will not run into trouble.
    * 
    * @access   public
    * @param    string      Needed Permission
    * @return   boolean     TRUE if access can be granted, FALSE if permission denied
    */
    public function checkPermission($ePermissionNeeded) {
        // Update authentication data before permission check
        self::updateAuthentication();
        
        // Permission DELETE check
        if($ePermissionNeeded == self::PERMISSION_DELETE && self::$ePermission == self::PERMISSION_DELETE) {
            return true;
        }
        
        // Permission WRITE check
        if($ePermissionNeeded == self::PERMISSION_WRITE && (self::$ePermission == self::PERMISSION_DELETE || self::$ePermission == self::PERMISSION_WRITE)) {
            return true;
        }
        
        // Permission READ check
        if($ePermissionNeeded == self::PERMISSION_READ && (self::$ePermission == self::PERMISSION_DELETE || self::$ePermission == self::PERMISSION_WRITE || self::$ePermission == self::PERMISSION_READ)) {
            return true;
        }
        
        return false;
    }
    
    /**
    * Proxy for API queries
    * 
    * Will check permission for the requested API method as well as type
    * of the object result response or exception. Will call the given
    * API query handler method (default: executeRemoteCall_CURL) for
    * the raw connection stuff
    * 
    * @access   public
    * @param    string          API method name
    * @param    array           Additional arguments that need to be passed to the API
    * @return   VimeoResponse   Response object of API corresponding query (for vimeo.test.login you will get VimeoTestLoginResponse object)
    */
    public function executeRemoteCall($sMethod, $aArgs = array()) {
        // Get exception handler
        $sExceptionClass = VimeoMethod::getExceptionObjectForMethod($sMethod);
        
        // Check for errors in parameters
        $sTargetClass = VimeoMethod::getTargetObjectForMethod($sMethod);
        
        // Get the permission needed to run this method
        $ePermissionNeeded = VimeoMethod::getPermissionRequirementForMethod($sMethod);
        
        // If permission requirement is not met refuse to even call the API, safes bandwith for both ends
        if($ePermissionNeeded != VimeoBase::PERMISSION_NONE && !self::checkPermission($ePermissionNeeded)) {
            throw new $sExceptionClass('Permission error: "' . VimeoMethod::getPermissionRequirementForMethod($sMethod) . '" needed, "' . self::$ePermission . '" given');
        }
        
        // Append method to request arguments
        $aArgs['method'] = $sMethod;
        
        // Check that the API query handler method exists and can be called
        if(!method_exists(__CLASS__, self::REQUEST_ENGINE_CURL)) {
            throw new VimeoBaseException('Internal error: Request engine handler method not found', 2);
        }
        
        // Build up the needed API arguments

        // Set API key
        $aArgs['api_key']       = self::$sApiKey;
        
        // Set request format
        $aArgs['format']        = 'php';

        // Set token
        if(self::$sToken) $aArgs['auth_token'] = self::$sToken;
        
        // Generate signature
        $aArgs['api_sig']       = self::buildSignature($aArgs);

        // Do the request
        $aResponse = call_user_func(array(__CLASS__, self::REQUEST_ENGINE_CURL), $aArgs);

        // Debug request
        if(defined('VIMEO_DEBUG_REQUEST') && VIMEO_DEBUG_REQUEST) {
            self::debug('API request', print_r($aArgs, true));
        }
        
        // Debug response
        if(defined('VIMEO_DEBUG_RESPONSE') && VIMEO_DEBUG_RESPONSE) {
            self::debug('API response', print_r($aResponse, true));
        }
        
        // Transform the result into a result class
        $oResult = new $sTargetClass($aResponse);
        
        // Check if request was successfull
        if(!$oResult->getStatus()) {
            // If not, create an given exception class for the given method and pass through error code and message
            throw new $sExceptionClass($oResult->getError()->getMessage(), $oResult->getError()->getCode());
        }
        
        // Return the base class object instance for the corresponding API query
        return $oResult;
    }
    
    /**
    * Execute raw API query with CURL
    * 
    * Implements CURL API queries in php format response
    * 
    * @author   Ted Roden
    * @access   private
    * @param    array       Additional arguments for the API query
    * @return   stdClass    Simple PHP object enclosing the API result
    */
    private function executeRemoteCall_CURL($aArgs) {
        $ch = curl_init(self::VIMEO_REST_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $aArgs);
        curl_setopt($ch, CURLOPT_USERAGENT, self::PROJECT_NAME);

        $data = curl_exec($ch);
        if(curl_errno($ch))
            throw new VimeoRequestException('executeRemoteCall_CURL error: ' . curl_error($ch), curl_errno($ch));
        else {
            curl_close($ch);
            
            if(!$data || strlen(trim($data)) < 2) {
                throw new VimeoRequestException('API request error: No result returned.', 1);
            }
            return unserialize($data);
        }
    }
    
    /**
    * Execute raw API query with FSOCK
    * 
    * Implements FSOCK API queries in php format response
    * 
    * @access   private
    * @param    array       Additional arguemnts for the API query
    * @return   stdClass    Simple PHP object enclosing the API result
    */
    private function executeRemoteCall_FSOCK($aArgs) {
        $sResponse = file_get_contents(self::VIMEO_REST_URL, NULL, stream_context_create(array('http' => array('method' => 'POST', 'header'=> 'Content-type: application/x-www-form-urlencoded', 'content' => http_build_query($aArgs)))));
        if(!$sResponse || strlen(trim($sResponse)) < 2) {
            throw new VimeoRequestException('API request error: No result returned.', 1);
        } else {
            return unserialize($sResponse);
        }
    }

    /**
    * Proxy for video uploads
    * 
    * Will call the given video upload handler method (default: executeVideopostCall_FSOCK)
    * for the raw connection and send stuff
    * 
    * @access   public
    * @param    string          Local filename to be transfered
    * @param    string          Ticket
    * @return   string          VimeoVideosCheckUploadStatusResponse
    */
    public function executeVideopostCall($sFilename, $sTicket = false) {
        // Check that the upload query handler method exists and can be called
        if(!method_exists(__CLASS__, self::VIDEOPOST_ENGINE_FSOCK)) {
            throw new VimeoUploadException('Upload error: Videopost engine handler method not found', 1);
        }
        
        // If permission requirement is not met refuse to even call the API, safes bandwith for both ends
        if(!self::checkPermission(VimeoBase::PERMISSION_WRITE)) {
            throw new VimeoUploadException('Upload error: Missing "write" permission for current user', 2);
        }

        // Check that the file exists
        if(!file_exists($sFilename)) {
            throw new VimeoUploadException('Upload error: Local file does not exists', 3);
        }
        
        // Check that the file is readable
        if(!is_readable($sFilename)) {
            throw new VimeoUploadException('Upload error: Local file is not readable', 4);
        }
        
        // Check that the file size is not larger then the allowed size you can upload
        $oResponse = VimeoPeopleRequest::getUploadStatus();
        if(filesize($sFilename) > $oResponse->getRemainingBytes()) {
            throw new VimeoUploadException('Upload error: Videosize exceeds remaining bytes', 5);
        }
        
        // Try to get a upload ticket
        if(!$sTicket) {
            $oResponse = VimeoVideosRequest::getUploadTicket();
            $sTicket = $oResponse->getTicket();
        }
        
        // Build up the needed API arguments

        // Set API key
        $aArgs['api_key']       = self::$sApiKey;
        
        // Set request format
        $aArgs['format']        = 'php';

        // Set token
        if(self::$sToken) $aArgs['auth_token'] = self::$sToken;
        
        // Set ticket
        $aArgs['ticket_id']     = $sTicket;
        
        // Generate signature
        $aArgs['api_sig']       = self::buildSignature($aArgs);
        
        // Set file
        $aArgs['file']          = "@$sFilename";
        
        // Do the upload
        $sResponse = call_user_func(array(__CLASS__, self::VIDEOPOST_ENGINE_FSOCK), $aArgs);

        // Call vimeo.videos.checkUploadStatus to prevent abandoned status
        return VimeoVideosRequest::checkUploadStatus($sTicket);
    }
    
    private function executeVideopostCall_CURL($aArgs) {
        // Disable time limit
        set_time_limit(0);
        
        $ch = curl_init(self::VIMEO_UPLOAD_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $aArgs);
        curl_setopt($ch, CURLOPT_USERAGENT, self::PROJECT_NAME);

        $data = curl_exec($ch);
        if(curl_errno($ch))
            throw new VimeoRequestException('executeRemoteCall_CURL error: ' . curl_error($ch), curl_errno($ch));
        else {
            curl_close($ch);
            return unserialize($data);
        }
    }
    
    /**
    * Build API query signature
    * 
    * Composes the signature needed to verify its really us doing the query
    * 
    * @author   Ted Roden
    * @access   private
    * @param    array       Additional arguments for the API query
    * @return   string      MD5 signature
    */
    private static function buildSignature($aArgs) {
        $s = '';
        
        // sort by name
        ksort($aArgs);

        foreach($aArgs as $k => $v) 
            $s .= $k . $v;

        return(md5(self::$sApiSecret . $s));    
    }
    
    /**
    * Build authentication URL
    * 
    * Easy way to build a correct authentication url. You can use this
    * to link the user directly to the correct vimeo authentication page.
    * 
    * @access   public
    * @param    string      Permission level you need the user to give you (i.e. VimeoBase::PERMISSION_READ)
    * @return   string      URL you can use to directly link the user to the vimeo authentication page
    */
    public static function buildAuthenticationUrl($ePermission) {
        
        $aArgs = array(
            'api_key' => self::$sApiKey,
            'perms' => $ePermission
        );
        
        return self::VIMEO_AUTH_URL . '?api_key=' . self::$sApiKey . '&perms=' . $ePermission . '&api_sig=' . self::buildSignature($aArgs);
    }
    
    /**
    * Get current logged in user token
    * 
    * @access   public
    * @return   string      Token or FALSE if not logged in
    */
    public static function getToken() {
        return self::$sToken;
    }
    
    /**
    * Set current logged in user token
    * 
    * @access   public
    * @param    string      Authentication token
    * @return   void
    */
    public static function setToken($sToken) {
        self::$sToken = $sToken;
    }
    
    /**
    * Clear current logged in user token
    * 
    * Removes the current logged in user from the cache. Next API query
    * will be made as clean, not logged in, request.
    * 
    * @access   public
    * @return   void
    */
    public static function clearToken() {
        self::$sToken = false;
    }
    
    /**
    * Execute a permit request
    * 
    * ONLY USED IN SITE-MODE, see howto.autologin.php
    * Permits the current CURL cached user with your vimeo API application
    * 
    * @access   public
    * @param    string      Permission
    * @return   string      Vimeo Token
    */
    public function permit($ePermission) {
        // Disable time limit
        set_time_limit(0);
        
        // Construct login data
        $aArgs = array(
            'api_key' => VimeoBase::$sApiKey,
            'perms' => $ePermission,
            'accept' => 'yes'
        );
        $ch = curl_init(VimeoBase::buildAuthenticationUrl($ePermission));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $aArgs);
        curl_setopt($ch, CURLOPT_USERAGENT, self::PROJECT_NAME);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, VimeoBase::COOKIE_FILE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, VimeoBase::COOKIE_FILE);
        
        $sPageContent = curl_exec($ch);
        if(curl_errno($ch)) {
            throw new VimeoRequestException('Error: Tried to login failed ' . curl_error($ch), curl_errno($ch));
            return false;
        } else {
            $sResponseUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            
        }
        return $sPageContent;
    }
    
    /**
    * Ensures that the user is logged in
    * 
    * ONLY USED IN SITE-MODE, see howto.autologin.php
    * Ensures the site-account is logged in
    * 
    * @access   public
    * @param    string          Username
    * @param    string          Password
    * @return   boolean         TRUE if user could be logged in, FALSE if an error occured (try manually to see error)
    */
    public function login($sUsername, $sPassword) {
        // Disable time limit
        set_time_limit(0);
        
        // Construct login data
        $aArgs = array(
            'sign_in[email]' => $sUsername,
            'sign_in[password]' => $sPassword,
            'redirect' => ''
        );
        
        $ch = curl_init(VimeoBase::VIMEO_LOGIN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $aArgs);
        curl_setopt($ch, CURLOPT_USERAGENT, self::PROJECT_NAME);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, VimeoBase::COOKIE_FILE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, VimeoBase::COOKIE_FILE);
        
        $sPageContent = curl_exec($ch);
        
        if(curl_errno($ch)) {
            throw new VimeoRequestException('Error: Tried to login failed ' . curl_error($ch), curl_errno($ch));
            return false;
        } else {
            $sResponseUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
        }                           
        
        if(stristr($sResponseUrl, 'log_in') !== false) {
            // Login failed
            return false;
        } else {
            return true;
        }
    }
}

/**
* Vimeo base exception class
*
* Every exception caused by VimeoBase class will be of this type
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoBaseException extends VimeoException {}

/**
* Vimeo request exception class
*
* Exception thrown when requesting the API failed
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoRequestException extends VimeoException {}

/**
* Vimeo upload exception class
*
* Exception thrown when uploading a video failed
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoUploadException extends VimeoException {}

/**
* Vimeo API method handler class
*
* This class will ensure that only functions can be called if the method is implemented
* and the permission is right (p). It also states what the source (s), result (t) and
* exception (e) object will be.
* 
* @package     SimpleVimeo
* @subpackage  Base
*/

class VimeoMethod {
    
    private static $aMethods = array(
        // Vimeo Test methods
        'vimeo.test.login'                  => array(       's' => 'VimeoTestRequest',
                                                            't' => 'VimeoTestLoginResponse',
                                                            'e' => 'VimeoTestLoginException',
                                                            'p' => VimeoBase::PERMISSION_READ),
        
        'vimeo.test.echo'                   => array(       's' => 'VimeoTestRequest',
                                                            't' => 'VimeoTestEchoResponse',
                                                            'e' => 'VimeoTestEchoException',
                                                            'p' => VimeoBase::PERMISSION_NONE),
                                                            
        'vimeo.test.null'                   => array(       's' => 'VimeoTestRequest',
                                                            't' => 'VimeoTestNullResponse',
                                                            'e' => 'VimeoTestNullException',
                                                            'p' => VimeoBase::PERMISSION_READ),
                                                            
        // Vimeo Auth methods
        'vimeo.auth.getToken'               => array(       's' => 'VimeoAuthRequest',
                                                            't' => 'VimeoAuthGetTokenResponse',
                                                            'e' => 'VimeoAuthGetTokenException',
                                                            'p' => VimeoBase::PERMISSION_NONE),
                                                            
        'vimeo.auth.getFrob'                => array(       's' => 'VimeoAuthRequest',
                                                            't' => 'VimeoAuthGetFrobResponse',
                                                            'e' => 'VimeoAuthGetFrobException',
                                                            'p' => VimeoBase::PERMISSION_NONE),

        'vimeo.auth.checkToken'             => array(       's' => 'VimeoAuthRequest',
                                                            't' => 'VimeoAuthCheckTokenResponse',
                                                            'e' => 'VimeoAuthCheckTokenException',
                                                            'p' => VimeoBase::PERMISSION_NONE),
        
        // Vimeo Videos methods
        'vimeo.videos.getList'              => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetListResponse',
                                                            'e' => 'VimeoVideosGetListException',
                                                            'p' => VimeoBase::PERMISSION_NONE),

        'vimeo.videos.getUploadedList'      => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetUploadedListResponse',
                                                            'e' => 'VimeoVideosGetUploadedListException',
                                                            'p' => VimeoBase::PERMISSION_NONE),

        'vimeo.videos.getAppearsInList'     => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetAppearsInListResponse',
                                                            'e' => 'VimeoVideosGetAppearsInListException',
                                                            'p' => VimeoBase::PERMISSION_NONE),
                                                            
        'vimeo.videos.getSubscriptionsList' => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetSubscriptionsListResponse',
                                                            'e' => 'VimeoVideosGetSubscriptionsListException',
                                                            'p' => VimeoBase::PERMISSION_NONE),

        'vimeo.videos.getListByTag'         => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetListByTagResponse',
                                                            'e' => 'VimeoVideosGetListByTagException',
                                                            'p' => VimeoBase::PERMISSION_NONE),

        'vimeo.videos.getLikeList'          => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetLikeListResponse',
                                                            'e' => 'VimeoVideosGetLikeListException',
                                                            'p' => VimeoBase::PERMISSION_NONE),

        'vimeo.videos.getContactsList'      => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetContactsListResponse',
                                                            'e' => 'VimeoVideosGetContactsListException',
                                                            'p' => VimeoBase::PERMISSION_NONE),

        'vimeo.videos.getContactsLikeList'  => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetContactsLikeListResponse',
                                                            'e' => 'VimeoVideosGetContactsLikeListException',
                                                            'p' => VimeoBase::PERMISSION_NONE),
                                                            
        'vimeo.videos.search'               => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosSearchResponse',
                                                            'e' => 'VimeoVideosSearchException',
                                                            'p' => VimeoBase::PERMISSION_NONE),
                                                            
        'vimeo.videos.getInfo'              => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetInfoResponse',
                                                            'e' => 'VimeoVideosGetInfoException',
                                                            'p' => VimeoBase::PERMISSION_NONE),
                                                            
        'vimeo.videos.getUploadTicket'      => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosGetUploadTicketResponse',
                                                            'e' => 'VimeoVideosGetUploadTicketException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),

        'vimeo.videos.checkUploadStatus'    => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosCheckUploadStatusResponse',
                                                            'e' => 'VimeoVideosCheckUploadStatusException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),
                                                            
        'vimeo.videos.delete'               => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosDeleteResponse',
                                                            'e' => 'VimeoVideosDeleteException',
                                                            'p' => VimeoBase::PERMISSION_DELETE),

        'vimeo.videos.setTitle'             => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosSetTitleResponse',
                                                            'e' => 'VimeoVideosSetTitleException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),

        'vimeo.videos.setCaption'           => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosSetCaptionResponse',
                                                            'e' => 'VimeoVideosSetCaptionException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),
                                                            
        'vimeo.videos.setFavorite'          => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosSetFavoriteResponse',
                                                            'e' => 'VimeoVideosSetFavoriteException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),
                                                            
        'vimeo.videos.addTags'              => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosAddTagsResponse',
                                                            'e' => 'VimeoVideosAddTagsException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),
                                                            
        'vimeo.videos.removeTag'            => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosRemoveTagResponse',
                                                            'e' => 'VimeoVideosRemoveTagException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),

        'vimeo.videos.clearTags'            => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosClearTagsResponse',
                                                            'e' => 'VimeoVideosClearTagsException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),

        'vimeo.videos.setPrivacy'           => array(       's' => 'VimeoVideosRequest',
                                                            't' => 'VimeoVideosSetPrivacyResponse',
                                                            'e' => 'VimeoVideosSetPrivacyException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),

        // Vimeo People methods
        'vimeo.people.findByUserName'       => array(       's' => 'VimeoPeopleRequest',
                                                            't' => 'VimeoPeopleFindByUsernameResponse',
                                                            'e' => 'VimeoPeopleFindByUsernameException',
                                                            'p' => VimeoBase::PERMISSION_NONE),

        'vimeo.people.findByEmail'          => array(       's' => 'VimeoPeopleRequest',
                                                            't' => 'VimeoPeopleFindByEmailResponse',
                                                            'e' => 'VimeoPeopleFindByEmailException',
                                                            'p' => VimeoBase::PERMISSION_NONE),

        'vimeo.people.getInfo'              => array(       's' => 'VimeoPeopleRequest',
                                                            't' => 'VimeoPeopleGetInfoResponse',
                                                            'e' => 'VimeoPeopleGetInfoException',
                                                            'p' => VimeoBase::PERMISSION_NONE),
                                                            
        'vimeo.people.getPortraitUrl'       => array(       's' => 'VimeoPeopleRequest',
                                                            't' => 'VimeoPeopleGetPortraitUrlResponse',
                                                            'e' => 'VimeoPeopleGetPortraitUrlException',
                                                            'p' => VimeoBase::PERMISSION_NONE),
        
        'vimeo.people.addContact'           => array(       's' => 'VimeoPeopleRequest',
                                                            't' => 'VimeoPeopleAddContactResponse',
                                                            'e' => 'VimeoPeopleAddContactException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),
                                                            
        'vimeo.people.removeContact'        => array(       's' => 'VimeoPeopleRequest',
                                                            't' => 'VimeoPeopleRemoveContactResponse',
                                                            'e' => 'VimeoPeopleRemoveContactException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),
                                                            
        'vimeo.people.getUploadStatus'      => array(       's' => 'VimeoPeopleRequest',
                                                            't' => 'VimeoPeopleGetUploadStatusResponse',
                                                            'e' => 'VimeoPeopleGetUploadStatusException',
                                                            'p' => VimeoBase::PERMISSION_READ),

        'vimeo.people.addSubscription'      => array(       's' => 'VimeoPeopleRequest',
                                                            't' => 'VimeoPeopleAddSubscriptionResponse',
                                                            'e' => 'VimeoPeopleAddSubscriptionException',
                                                            'p' => VimeoBase::PERMISSION_WRITE),

        'vimeo.people.removeSubscription'   => array(       's' => 'VimeoPeopleRequest',
                                                            't' => 'VimeoPeopleRemoveSubscriptionResponse',
                                                            'e' => 'VimeoPeopleRemoveSubscriptionException',
                                                            'p' => VimeoBase::PERMISSION_WRITE)
    );
        
    public static function getSourceObjectForMethod($sMethod) {
        // Check if the method can be handled
        self::checkMethod($sMethod);
        
        return self::$aMethods[$sMethod]['s'];
    }
    
    public static function getTargetObjectForMethod($sMethod) {
        // Check if the method can be handled
        self::checkMethod($sMethod);
        
        return self::$aMethods[$sMethod]['t'];
    }
    
    public static function getExceptionObjectForMethod($sMethod) {
        // Check if the method can be handled
        self::checkMethod($sMethod);
        
        return self::$aMethods[$sMethod]['e'];
    }

    public static function getPermissionRequirementForMethod($sMethod) {
        // Check if the method can be handled
        self::checkMethod($sMethod);
        
        return self::$aMethods[$sMethod]['p'];
    }
    
    public static function checkMethod($sMethod) {
        // Check if the method can be handled
        if(!isset(self::$aMethods[$sMethod])) {
            throw new VimeoMethodException('Unhandled vimeo method "' . $sMethod . '" given', 2);
        }
    }
}

/**
* Vimeo method exception class
*
* Every exception caused by VimeoMethod class will be of this type
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoMethodException extends Exception {}

/*
* Abstract class constructs that the whole api stuff will be based on
*/

/**
* Vimeo exception class
*
* Every exception the whole SimpleVimeo throws will be extended of this base
* class. You can extend this one to alter all exceptions.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
* @abstract
*/

abstract class VimeoException extends Exception {}

/**
* Vimeo array of object handler class
* 
* This class is for array of object handling. i.e.: An array of video objects.
* It ensures that you can work with foreach and count without getting into a hassle.
* 
* @package      SimpleVimeo
* @subpackage   Base
* @abstract
*/

abstract class VimeoObjectList implements Iterator, Countable {
    /**
    * Array for instanced objects
    * @var  array
    */
    private $aInstances = array();
    
    /**
    * Integer how many results
    * @var  integer
    */
    private $iCount = 0;
    
    /**
    * Class name
    * @var  string
    */
    private $sClassName;
    
    private $aIDs = array();
    
    /**
    * Constructor
    * 
    * @access   public
    * @return   void
    */
    public function __construct() {
        // Parse class name
        $this->sClassName = str_replace('List', '', get_class($this));
    }
    
    /**
    * Add object to array
    * 
    * @access   public
    * @param    object      Object to be added to array
    * @param    integer     Array index to be used for the given object
    * @return   void
    */
    public function add($oObject, $iID = false) {
        if($iID !== false) {
            $this->aInstances[$iID] = $oObject;
        } else {
            $this->aInstances[] = $oObject;
        }
        
        $this->aIDs[] = $iID;
        
        $this->iCount++;
    }

    /**
    * Returns all array indexes for further parsing
    * 
    * @access   public
    * @return   array       Array with object array indexes
    */
    public function getAllUniqueIDs() {
        return $this->getIDs();
    }
    
    /**
    * @ignore
    */
    public function rewind() {
        reset($this->aInstances);
    }
    
    /**
    * @ignore
    */
    public function current() {
        return current($this->aInstances);
    }
    
    /**
    * @ignore
    */
    public function key() {
        return key($this->aInstances);
    }
    
    /**
    * @ignore
    */
    public function next() {
        return next($this->aInstances);
    }
    
    /**
    * @ignore
    */
    public function valid() {
        return $this->current() !== FALSE;
    }
    
    /**
    * @ignore
    */
    public function count() {
        return $this->iCount;
    }
}

/**
* Vimeo request class
*
* Every API query collection class will be based on this.
* 
* @package     SimpleVimeo
* @subpackage  ApiRequest
* @abstract
*/

abstract class VimeoRequest {}

/**
* Vimeo response class
*
* Every API response class will be based on this. It also handles
* everytime response variables like if the query was successfull and
* the generation time.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
* @abstract
*/

abstract class VimeoResponse {
    private $bStatus = false;
    private $fPerformance = false;
    private $iErrorCode = false;
    private $oError = false;
    
    /**
    * Constructor
    * 
    * Parses the API response
    * You dont need to pass a response if you need to give a hint your coding tool for code completion
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse = false) {
        if($aResponse) {
            // Parse status
            $this->setStatus($aResponse->stat);
            
            // Parse performance
            $this->fPerformance = (float) $aResponse->generated_in;
            
            // Parse error information
            if(!$this->bStatus) {
                $this->oError = new VimeoErrorEntity($aResponse->err->code, $aResponse->err->msg);
            }
        }
    }
    
    private function setStatus($sStatus) {
        if($sStatus === 'ok') {
            $this->bStatus = true;
        }
    }
    
    public function getStatus() {
        return $this->bStatus;
    }
    
    public function getPerformance() {
        return $this->fPerformance;
    }
    
    public function getError() {
        return $this->oError;
    }
}

/*
* Entity classes for default instances of users etc. they are always the same
* and their array of object handlers
*/

/**
* Vimeo API error entity class
*
* Implements API delivered error entities into an PHP 5 object with given result parameters.
* 
* @package     SimpleVimeo
* @subpackage  Entities
*/

class VimeoErrorEntity {
    private $iErrorCode = false;
    private $sErrorMessage = false;
    
    public function __construct($iErrorCode, $sErrorMessage) {
        $this->iErrorCode = $iErrorCode;
        $this->sErrorMessage = $sErrorMessage;
    }
    
    public function getCode() {
        return $this->iErrorCode;
    }
    
    public function getMessage() {
        return $this->sErrorMessage;
    }
}

/**
* Vimeo API user entity class
*
* Implements API delivered user entities into an PHP 5 object with given result parameters.
* 
* @package     SimpleVimeo
* @subpackage  Entities
*/

class VimeoUserEntity {
    private $iUserNsId = false;
    private $iUserId = false;
    private $sUsername = false;
    private $sFullname = false;
    
    // Optional information when vimeo.person.getInfo is called
    private $sLocation = false;
    private $sUrl = false;
    private $iNumberOfContacts = false;
    private $iNumberOfUploads = false;
    private $iNumberOfLikes = false;
    private $iNumberOfVideos = false;
    private $iNumberOfVideosAppearsIn = false;
    private $sProfileUrl = false;
    private $sVideosUrl = false;
    
    public function __construct($aResponseSnippet) {
        if(isset($aResponseSnippet->id)) {
            $this->iUserId = $aResponseSnippet->id;
        }
        
        if(isset($aResponseSnippet->nsid)) {
            $this->iUserNsId = $aResponseSnippet->nsid;
        }
        
        if(isset($aResponseSnippet->username)) {
            $this->sUsername = $aResponseSnippet->username;
        }
        
        if(isset($aResponseSnippet->fullname)) {
            $this->sFullname = $aResponseSnippet->fullname;
        }
        
        if(isset($aResponseSnippet->display_name)) {
            $this->sFullname = $aResponseSnippet->display_name;
        }
        
        // Optional stuff
        if(isset($aResponseSnippet->location)) {
            $this->sLocation = $aResponseSnippet->location;
        }
        
        if(isset($aResponseSnippet->url)) {
            $this->sUrl = $aResponseSnippet->url;
        }
        
        if(isset($aResponseSnippet->number_of_contacts)) {
            $this->iNumberOfContacts = $aResponseSnippet->number_of_contacts;
        }
        
        if(isset($aResponseSnippet->number_of_uploads)) {
            $this->iNumberOfUploads = $aResponseSnippet->number_of_uploads;
        }
        
        if(isset($aResponseSnippet->number_of_likes)) {
            $this->iNumberOfLikes = $aResponseSnippet->number_of_likes;
        }
        
        if(isset($aResponseSnippet->number_of_videos)) {
            $this->iNumberOfVideos = $aResponseSnippet->number_of_videos;
        }
        
        if(isset($aResponseSnippet->number_of_videos_appears_in)) {
            $this->iNumberOfVideosAppearsIn = $aResponseSnippet->number_of_videos_appears_in;
        }
        
        if(isset($aResponseSnippet->profileurl)) {
            $this->sProfileUrl = $aResponseSnippet->profileurl;
        }

        if(isset($aResponseSnippet->videosurl)) {
            $this->sVideosUrl = $aResponseSnippet->videosurl;
        }
    }
    
    public function getNsID() {
        return $this->iUserNsId;
    }

    public function getID() {
        return $this->iUserId;
    }
    
    public function getUsername() {
        return $this->sUsername;
    }
    
    public function getFullname() {
        return $this->sFullname;
    }
    
    public function getLocation() {
        return $this->sLocation;
    }
    
    public function getUrl() {
        return $this->sUrl;
    }
    
    public function getNumberOfContacts() {
        return $this->iNumberOfContacts;
    }
    
    public function getNumberOfUploads() {
        return $this->iNumberOfUploads;
    }
    
    public function getNumberOfLikes() {
        return $this->iNumberOfLikes;
    }
    
    public function getNumberOfVideos() {
        return $this->iNumberOfVideos;
    }
    
    public function getNumberOfVideosAppearsIn() {
        return $this->iNumberOfVideosAppearsIn;
    }
    
    public function getProfileUrl() {
        return $this->sProfileUrl;
    }
    
    public function getVideosUrl() {
        return $this->sVideosUrl;
    }
}

/**
* Vimeo API video entity class
*
* Implements API delivered video into an PHP 5 object with given result parameters.
* 
* @package     SimpleVimeo
* @subpackage  Entities
*/

class VimeoVideoEntity {
    private $iID = false;
    private $ePrivacy = false;
    private $bIsUploading = false;
    private $bIsTranscoding = false;
    private $bIsHD = false;
    
    private $sTitle = false;
    private $sCaption = false;
    private $iUploadTime = false;
    private $iNumberOfLikes = false;
    private $iNumberOfPlays = false;
    private $iNumberOfComments = false;

    private $sUrl = false;
        
    private $iWidth = false;
    private $iHeight = false;
    private $oOwner = false;
    
    private $oTagList = false;
    
    private $oThumbnailList = false;
    
    public function __construct($aResponseSnippet = false) {
        if($aResponseSnippet) {
            // Set basic information
            $this->iID = $aResponseSnippet->id;
            $this->ePrivacy = $aResponseSnippet->privacy;
            $this->bIsUploading = $aResponseSnippet->is_uploading;
            $this->bIsTranscoding = $aResponseSnippet->is_transcoding;
            $this->bIsHD = $aResponseSnippet->is_hd;
            
            $this->sTitle = $aResponseSnippet->title;
            $this->sCaption = $aResponseSnippet->caption;
            $this->iUploadTime = strtotime($aResponseSnippet->upload_date);
            $this->iNumberOfLikes = (int) $aResponseSnippet->number_of_likes;
            $this->iNumberOfPlays = (int) $aResponseSnippet->number_of_plays;
            $this->iNumberOfComments = (int) $aResponseSnippet->number_of_comments;
            
            $this->sUrl = $aResponseSnippet->urls->url->_content;
            
            $this->iWidth = (int) $aResponseSnippet->width;
            $this->iHeight = (int) $aResponseSnippet->height;
            
            $this->oOwner = new VimeoUserEntity($aResponseSnippet->owner);
            
            // Parse Tags
            $this->oTagList = new VimeoTagList();
            if(isset($aResponseSnippet->tags->tag)) {
                foreach($aResponseSnippet->tags->tag as $aTagInformation) {
                    $oTag = new VimeoTagEntity($aTagInformation);
                    $this->oTagList->add($oTag, $oTag->getID());
                }
            }
            
            // Parse Thumbnails
            $this->oThumbnailList = new VimeoThumbnailList();
            if(isset($aResponseSnippet->thumbnails->thumbnail)) {
                foreach($aResponseSnippet->thumbnails->thumbnail as $aThumbnailInformation) {
                    $oThumbnail = new VimeoThumbnailEntity($aThumbnailInformation);
                    $this->oThumbnailList->add($oThumbnail, ($oThumbnail->getWidth() * $oThumbnail->getHeight()));
                }
            }
        }
    }
    
    public function getID() {
        return $this->iID;
    }
    
    public function getPrivacy() {
        return $this->ePrivacy;
    }
    
    public function isUploading() {
        return $this->bIsUploading;
    }
    
    public function isTranscoding() {
        return $this->bIsTranscoding;
    }
    
    public function isHD() {
        return $this->bIsHD;
    }
    
    public function getTitle() {
        return $this->sTitle;
    }
    
    public function getCaption() {
        return $this->sCaption;
    }
    
    public function getUploadTimestamp() {
        return $this->iUploadTime;
    }
    
    public function getNumberOfLikes() {
        return (int) $this->iNumberOfLikes;
    }
    
    public function getNumberOfPlays() {
        return (int) $this->iNumberOfPlays;
    }
    
    public function getNumberOfComments() {
        return (int) $this->iNumberOfComments;
    }
    
    public function getWidth() {
        return (int) $this->iWidth;
    }
    
    public function getHeight() {
        return (int) $this->iHeight;
    }
    
    public function getOwner() {
        return $this->oOwner;
    }
    
    public function getTags() {
        return $this->oTagList;
    }
    
    public function getUrl() {
        return $this->sUrl;
    }
    
    public function getThumbnails() {
        return $this->oThumbnailList;
    }
}

/**
* Vimeo API video list class
*
* Implements API delivered video list entities into an PHP 5 array of objects.
* 
* @package     SimpleVimeo
* @subpackage  Lists
*/

class VimeoVideoList extends VimeoObjectList {}

/**
* Vimeo API tag entity class
*
* Implements API delivered tag entities into an PHP 5 object with given result parameters.
* 
* @package     SimpleVimeo
* @subpackage  Entities
*/

class VimeoTagEntity {
    private $iID = false;
    private $sContent = false;
    
    public function __construct($aResponseSnippet = false) {
        if($aResponseSnippet) {
            $this->iID = $aResponseSnippet->id;
            $this->sContent = $aResponseSnippet->_content;
        }
    }
    
    public function getID() {
        return $this->iID;
    }
    
    public function getTag() {
        return $this->sContent;
    }
}

/**
* Vimeo API tag list class
*
* Implements API delivered tag list entities into an PHP 5 array of objects.
* 
* @package     SimpleVimeo
* @subpackage  Lists
*/

class VimeoTagList extends VimeoObjectList {}

/**
* Vimeo API thumbnail entity class
*
* Implements API delivered thumbnail entities into an PHP 5 object with given result parameters.
* 
* @package     SimpleVimeo
* @subpackage  Entities
*/

class VimeoThumbnailEntity {
    private $iWidth = false;
    private $iHeight = false;
    private $sContent = false;
    
    public function __construct($aResponseSnippet = false) {
        if($aResponseSnippet) {
            $this->iWidth = (int) $aResponseSnippet->width;
            $this->iHeight = (int) $aResponseSnippet->height;
            $this->sContent = $aResponseSnippet->_content;
        }
    }
    
    public function getWidth() {
        return (int) $this->iWidth;
    }
    
    public function getHeight() {
        return (int) $this->iHeight;
    }
}

/**
* Vimeo API thumbnail list class
*
* Implements API delivered thumbnail list entities into an PHP 5 array of objects.
* 
* @package     SimpleVimeo
* @subpackage  Lists
*/

class VimeoThumbnailList extends VimeoObjectList {
    public function getByWidth($iWidth, $bAlsoLower = false) {
        /**
        * @todo
        */
    }
    
    public function getByHeight($iHeight, $bAlsoLower = false) {
        /**
        * @todo
        */
    }
    
    public function getByWidthAndHeight($iWidth, $iHeight, $bAlsoLower = false) {
        /**
        * @todo
        */
    }
}


/*
* vimeo.test.* methods
*/

/**
* Vimeo Test request handler class
*
* Implements all API queries in the vimeo.test.* category
* 
* @package     SimpleVimeo
* @subpackage  ApiRequest
*/

class VimeoTestRequest extends VimeoRequest {
    
    /**
    * Is the user logged in?
    * 
    * @access   public
    * @return   VimeoTestLoginResponse 
    */
    
    public function login() {
        return VimeoBase::executeRemoteCall('vimeo.test.login');
    }
    
    /**
    * This will just repeat back any parameters that you send.
    * 
    * @access   public
    * @param    array       Additional arguments that need to be passed to the API
    * @return   VimeoTestEchoResponse
    */
    
    public function echoback($aArgs) {
        return VimeoBase::executeRemoteCall('vimeo.test.echo', $aArgs);
    }
    
    /**
    * This is just a simple null/ping test...
    * 
    * @access   public
    * @return   VimeoTestNullResponse 
    */
    
    public function ping() {
        return VimeoBase::executeRemoteCall('vimeo.test.null');
    }
}

/**
* Vimeo Test Login response handler class
*
* Handles the API response for vimeo.test.login queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoTestLoginResponse extends VimeoResponse {}

/**
* Vimeo Test Login exception handler class
*
* Handles exceptions caused by API response for vimeo.test.login queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoTestLoginException extends VimeoException {}

/**
* Vimeo Test Echo response handler class
*
* Handles the API response for vimeo.test.echo queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoTestEchoResponse extends VimeoResponse {
    private $aArgs = false;

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse = false) {
        parent::__construct($aResponse);
        
        $this->aArgs = get_object_vars($aResponse);
        
        // Unset default response stuff
        if(isset($this->aArgs['stat'])) unset($this->aArgs['stat']);
        if(isset($this->aArgs['generated_in'])) unset($this->aArgs['generated_in']);
    }
    
    /**
    * Returns an array of variables the request bounced back
    * 
    * @access   public
    * @return   array       Echoed variables
    */
    public function getResponseArray() {
        return $this->aArgs;
    }
}

/**
* Vimeo Test Echo exception handler class
*
* Handles exceptions caused by API response for vimeo.test.echo queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoTestEchoException extends VimeoException {}

/**
* Vimeo Test Null response handler class
*
* Handles the API response for vimeo.test.null queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoTestNullResponse extends VimeoResponse {}

/**
* Vimeo Test Null exception handler class
*
* Handles exceptions caused by API response for vimeo.test.null queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoTestNullException extends VimeoException {}

/*
* vimeo.auth.* methods
*/

/**
* Vimeo Auth request handler class
*
* Implements all API queries in the vimeo.auth.* category
* 
* @package     SimpleVimeo
* @subpackage  ApiRequest
*/

class VimeoAuthRequest extends VimeoRequest {
    
    /**
    * Get Token
    * 
    * @access   public
    * @param    string      Frob taken from the vimeo authentication
    * @return   VimeoAuthGetTokenResponse
    */
    public function getToken($sFrob) {
        $aArgs = array(
            'frob' => $sFrob
        );
        
        return VimeoBase::executeRemoteCall('vimeo.auth.getToken', $aArgs);
    }
    
    /**
    * Check Token
    * 
    * Checks the validity of the token. Returns the user associated with it.
    * Returns the same as vimeo.auth.getToken
    * 
    * @access   public
    * @param    string      Authentication token
    * @return   VimeoAuthCheckTokenResponse
    */
    public function checkToken($sToken = false) {
        if(!$sToken) $sToken = VimeoBase::getToken();
        
        $aArgs = array(
            'auth_token' => $sToken
        );
        
        return VimeoBase::executeRemoteCall('vimeo.auth.checkToken', $aArgs);
    }
    
    /**
    * Get Frob
    * 
    * This is generally used by desktop applications. If the user doesn't already have
    * a token, you'll need to get the frob, send it to us at /services/auth. Then,
    * after the user, clicks continue on your app, you call vimeo.auth.getToken($frob)
    * and we give you the actual token. 
    * 
    * @access   public
    * @return   VimeoAuthGetFrobResponse
    */
    public function getFrob() {
        return VimeoBase::executeRemoteCall('vimeo.auth.getFrob');
    } 
}

/**
* Vimeo Auth GetToken response handler class
*
* Handles the API response for vimeo.auth.getToken queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoAuthGetTokenResponse extends VimeoResponse {
    
    private $sToken = false;
    private $ePermission = false;
    private $oUser = false;

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse) {
        parent::__construct($aResponse);
        
        $this->sToken = $aResponse->auth->token;
        $this->ePermission = $aResponse->auth->perms;
        
        $this->oUser = new VimeoUserEntity($aResponse->auth->user);
    }
    
    /**
    * Get token value
    * 
    * @access   public
    * @return   token
    */
    public function getToken() {
        return $this->sToken;
    }
    
    /**
    * Get permission value
    * 
    * @access   public
    * @return   permission
    */
    
    public function getPermission() {
        return $this->ePermission;
    }
    
    /**
    * Get user information object
    * 
    * @access   public
    * @return VimeoUserEntity
    */
    public function getUser() {
        return $this->oUser;
    }
}

/**
* Vimeo Auth GetToken exception handler class
*
* Handles exceptions caused by API response for vimeo.auth.getToken queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoAuthGetTokenException extends Exception {}

/**
* Vimeo Auth CheckToken response handler class
*
* Handles the API response for vimeo.auth.checkToken queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoAuthCheckTokenResponse extends VimeoAuthGetTokenResponse {}

/**
* Vimeo Auth CheckToken exception handler class
*
* Handles exceptions caused by API response for vimeo.auth.checkToken queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoAuthCheckTokenException extends VimeoAuthGetTokenException {}

/**
* Vimeo Auth GetFrob response handler class
*
* Handles the API response for vimeo.auth.getFrob queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoAuthGetFrobResponse extends VimeoResponse {
    private $sFrob = false;
    
    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse) {
        parent::__construct($aResponse);
        
        $this->sFrob = $aResponse->frob;
    }
    
    /**
    * Get Frob value
    * 
    * @access   public
    * @return   frob
    */
    public function getFrob() {
        return $this->sFrob;
    }
}

/**
* Vimeo Auth GetFrob exception handler class
*
* Handles exceptions caused by API response for vimeo.auth.getFrob queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoAuthGetFrobException extends VimeoException {}

/**
* vimeo.videos.* methods
*/

/**
* Vimeo Videos request handler class
*
* Implements all API queries in the vimeo.videos.* category
* 
* @package     SimpleVimeo
* @subpackage  ApiRequest
*/

class VimeoVideosRequest extends VimeoRequest {

    const PRIVACY_ANYBODY = 'anybody';
    const PRIVACY_CONTACTS = 'contacts';
    const PRIVACY_NOBODY = 'nobody';
    const PRIVACY_USERS = 'users';
    
    /**
    * Search videos! 
    * 
    * If the calling user is logged in, this will return information that calling user
    * has access to (including private videos). If the calling user is not authenticated,
    * this will only return public information, or a permission denied error if none is available.
    * 
    * @access   public
    * @param    string      Search query
    * @param    integer     User ID, this can be the ID number (151542) or the username (ted)
    * @param    boolean     If TRUE, we'll only search the users contacts. If this is set, you must specifiy a User ID. Otherwise it will be ignored without error.
    * @param    integer     ow many results per page?
    * @return VimeoVideosSearchResponse  
    */
    public function search($sQuery, $iUserID = false, $bContactsOnly = false, $iItemsPerPage = false) {
        
        // Pass query (required)
        $aArgs = array(
            'query' => $sQuery
        );
        
        // Pass user
        if($iUserID) {
            $aArgs['user_id'] = $iUserID;
        }
        
        // Pass contacts
        if($bContactsOnly) {
            $aArgs['contacts_only'] = $bContactsOnly;
        }
        
        // Pass items
        if($iItemsPerPage) {
            $aArgs['per_page'] = $iItemsPerPage;
        }
        
        // Please deliver full response so we can handle videos with unified classes
        $aArgs['fullResponse'] = 1;
        
        return VimeoBase::executeRemoteCall('vimeo.videos.search', $aArgs);
    }
    
    /**
    * This gets a list of videos for the specified user.
    * 
    * This is the functionality of "My Videos" or "Ted's Videos." At the moment, this is the same list
    * as vimeo.videos.getAppearsInList. If you need uploaded or appears in, those are available too.
    * 
    * @access   public
    * @param    integer     User ID, this can be the ID number (151542) or the username (ted)
    * @param    integer     Which page to show.
    * @param    integer     How many results per page?
    * @return   VimeoVideosGetListResponse
    */
    public function getList($iUserID, $iPage = false, $iItemsPerPage = false) {
        // Extend query
        $aArgs = array(
            'user_id'       => $iUserID
        );
        
        if($iPage) {
            $aArgs['page'] =    $iPage;
        }

        if($iItemsPerPage) {
            $aArgs['per_page'] = $iItemsPerPage;
        }
        
        // Please deliver full response so we can handle videos with unified classes
        $aArgs['fullResponse'] = 1;
        
        return VimeoBase::executeRemoteCall('vimeo.videos.getList', $aArgs);
    }
    
    /**
    * This gets a list of videos uploaded by the specified user.
    * 
    * If the calling user is logged in, this will return information that calling user has access to
    * (including private videos). If the calling user is not authenticated, this will only return
    * public information, or a permission denied error if none is available.
    * 
    * @access   public
    * @param    integer     User ID, this can be the ID number (151542) or the username (ted)
    * @param    integer     Which page to show.
    * @param    integer     How many results per page?
    * @return   VimeoVideosGetUploadedListResponse
    */
    public function getUploadedList($iUserID, $iPage = false, $iItemsPerPage = false) {
        // Extend query
        $aArgs = array(
            'user_id'       => $iUserID
        );
        
        if($iPage) {
            $aArgs['page'] =    $iPage;
        }

        if($iItemsPerPage) {
            $aArgs['per_page'] = $iItemsPerPage;
        }
        
        // Please deliver full response so we can handle videos with unified classes
        $aArgs['fullResponse'] = 1;
        
        return VimeoBase::executeRemoteCall('vimeo.videos.getUploadedList', $aArgs);
    }
    
    /**
    * This gets a list of videos that the specified user appears in. 
    * 
    * If the calling user is logged in, this will return information that calling user has access
    * to (including private videos). If the calling user is not authenticated, this will only return
    * public information, or a permission denied error if none is available. 
    * 
    * @access   public
    * @param    integer     User ID, this can be the ID number (151542) or the username (ted)
    * @param    integer     Which page to show.
    * @param    integer     How many results per page?
    * @return   VimeoVideosGetAppearsInListResponse
    */
    public function getAppearsInList($iUserID, $iPage = false, $iItemsPerPage = false) {
        // Extend query
        $aArgs = array(
            'user_id'       => $iUserID
        );
        
        if($iPage) {
            $aArgs['page'] =    $iPage;
        }

        if($iItemsPerPage) {
            $aArgs['per_page'] = $iItemsPerPage;
        }
        
        // Please deliver full response so we can handle videos with unified classes
        $aArgs['fullResponse'] = 1;
        
        return VimeoBase::executeRemoteCall('vimeo.videos.getAppearsInList', $aArgs);
    }
    
    /**
    * This gets a list of subscribed videos for a particular user.  
    * 
    * If the calling user is logged in, this will return information that calling user
    * has access to (including private videos). If the calling user is not authenticated,
    * this will only return public information, or a permission denied error if none is available.
    * 
    * @access   public
    * @param    integer     User ID, this can be the ID number (151542) or the username (ted)
    * @param    integer     Which page to show.
    * @param    integer     How many results per page?
    * @return   VimeoVideosGetSubscriptionsListResponse
    */
    public function getSubscriptionsList($iUserID, $iPage = false, $iItemsPerPage = false) {
        // Extend query
        $aArgs = array(
            'user_id'       => $iUserID
        );
        
        if($iPage) {
            $aArgs['page'] =    $iPage;
        }

        if($iItemsPerPage) {
            $aArgs['per_page'] = $iItemsPerPage;
        }
        
        // Please deliver full response so we can handle videos with unified classes
        $aArgs['fullResponse'] = 1;
        
        return VimeoBase::executeRemoteCall('vimeo.videos.getSubscriptionsList', $aArgs);
    }

    /**
    * This gets a list of videos by tag    
    * 
    * If you specify a user_id, we'll only get video uploaded by that user with the specified tag.
    * If the calling user is logged in, this will return information that calling user has access
    * to (including private videos). If the calling user is not authenticated, this will only
    * return public information, or a permission denied error if none is available.
    * 
    * @access   public
    * @param    string      A single tag: "cat" "new york" "cheese" 
    * @param    integer     User ID, this can be the ID number (151542) or the username (ted)
    * @param    integer     Which page to show.
    * @param    integer     How many results per page?
    * @return   VimeoVideosGetListByTagResponse
    */
    public function getListByTag($sTag, $iUserID = false, $iPage = false, $iItemsPerPage = false) {
        // Extend query
        $aArgs = array(
            'tag'       => $sTag
        );
        
        if($iUserID) {
            $aArgs['user_id'] = $iUserID;
        }
        
        if($iPage) {
            $aArgs['page'] =    $iPage;
        }

        if($iItemsPerPage) {
            $aArgs['per_page'] = $iItemsPerPage;
        }
        
        // Please deliver full response so we can handle videos with unified classes
        $aArgs['fullResponse'] = 1;
        
        return VimeoBase::executeRemoteCall('vimeo.videos.getListByTag', $aArgs);
    }

    /**
    * Get a list of videos that the specified user likes.   
    * 
    * If the calling user is logged in, this will return information that calling user has
    * access to (including private videos). If the calling user is not authenticated, this will
    * only return public information, or a permission denied error if none is available.
    * 
    * @access   public
    * @param    integer     User ID, this can be the ID number (151542) or the username (ted)
    * @param    integer     Which page to show.
    * @param    integer     How many results per page?
    * @return   VimeoVideosGetLikeListResponse
    */
    public function getLikeList($iUserID, $iPage = false, $iItemsPerPage = false) {
        // Extend query
        $aArgs = array(
            'user_id'       => $iUserID
        );
        
        if($iPage) {
            $aArgs['page'] =    $iPage;
        }

        if($iItemsPerPage) {
            $aArgs['per_page'] = $iItemsPerPage;
        }
        
        // Please deliver full response so we can handle videos with unified classes
        $aArgs['fullResponse'] = 1;
        
        return VimeoBase::executeRemoteCall('vimeo.videos.getLikeList', $aArgs);
    }
    
    /**
    * Get a list of videos made by the contacts of a specific user.
    * 
    * If the calling user is logged in, this will return information that calling user has
    * access to (including private videos). If the calling user is not authenticated, this will
    * only return public information, or a permission denied error if none is available.
    * 
    * @access   public
    * @param    integer     User ID, this can be the ID number (151542) or the username (ted)
    * @param    integer     Which page to show.
    * @param    integer     How many results per page?
    * @return   VimeoVideosGetContactsListResponse
    */
    public function getContactsList($iUserID, $iPage = false, $iItemsPerPage = false) {
        // Extend query
        $aArgs = array(
            'user_id'       => $iUserID
        );
        
        if($iPage) {
            $aArgs['page'] =    $iPage;
        }

        if($iItemsPerPage) {
            $aArgs['per_page'] = $iItemsPerPage;
        }
        
        // Please deliver full response so we can handle videos with unified classes
        $aArgs['fullResponse'] = 1;
        
        return VimeoBase::executeRemoteCall('vimeo.videos.getContactsList', $aArgs);
    }
    
    /**
    * Get a list of videos that the specified users contacts like.
    * 
    * If the calling user is logged in, this will return information that calling user has
    * access to (including private videos). If the calling user is not authenticated, this will
    * only return public information, or a permission denied error if none is available.
    * 
    * @access   public
    * @param    integer     User ID, this can be the ID number (151542) or the username (ted)
    * @param    integer     Which page to show.
    * @param    integer     How many results per page?
    * @return   VimeoVideosGetContactsLikeListResponse
    */
    public function getContactsLikeList($iUserID, $iPage = false, $iItemsPerPage = false) {
        // Extend query
        $aArgs = array(
            'user_id'       => $iUserID
        );
        
        if($iPage) {
            $aArgs['page'] =    $iPage;
        }

        if($iItemsPerPage) {
            $aArgs['per_page'] = $iItemsPerPage;
        }
        
        // Please deliver full response so we can handle videos with unified classes
        $aArgs['fullResponse'] = 1;
        
        return VimeoBase::executeRemoteCall('vimeo.videos.getContactsLikeList', $aArgs);
    }
    
    /**
    * Get all kinds of information about a photo.
    * 
    * If the calling user is logged in, this will return information that calling user has
    * access to (including private videos). If the calling user is not authenticated, this will
    * only return public information, or a permission denied error if none is available.
    * 
    * @access   public
    * @param    integer     Video ID
    * @return   VimeoVideosGetInfoResponse
    */
    public function getInfo($iVideoID) {
        // Extend query
        $aArgs = array(
            'video_id'      => $iVideoID
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.getInfo', $aArgs);
    }
    
    /**
    * Generate a new upload Ticket.
    * 
    * You'll need to pass this to the uploader. It's only good for one upload, only good for one user.
    * 
    * @access   public
    * @return   VimeoVideosGetUploadTicketResponse
    */
    public function getUploadTicket() {
        return VimeoBase::executeRemoteCall('vimeo.videos.getUploadTicket');
    }
    
    /**
    * Check the status of an upload started via the API  
    * 
    * This is how you get the video_id of a clip uploaded from the API
    * If you never call this to check in, we assume it was abandoned and don't process it
    * 
    * @access   public
    * @param    string      The ticket number of the upload
    * @return   VimeoVideosCheckUploadStatusResponse
    */
    public function checkUploadStatus($sTicket) {
        $aArgs = array(
            'ticket_id' => $sTicket
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.checkUploadStatus', $aArgs);
    }
    
    /**
    * Simple video upload
    * 
    * @access   public
    * @param    string      Absolute path to file
    * @param    string      Existing ticket or false to generate a new one
    * @return   VimeoVideosCheckUploadStatusResponse
    */
    public function doUpload($sFilename, $sTicket = false) {
        return VimeoBase::executeVideopostCall($sFilename, $sTicket);
    }
    
    /**
    * Delete a video
    * 
    * The authenticated user must own the video and have granted delete permission
    * 
    * @access   public
    * @param    integer     Video ID
    * @return   VimeoVideosDeleteResponse 
    */
    public function delete($iVideoID) {
        $aArgs = array(
            'video_id' => $iVideoID
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.delete', $aArgs);
    }
    
    /**
    * Set the title of a video (overwrites previous title)
    * 
    * @access   public
    * @param    integer     Video ID
    * @param    string      Title
    * @return   VimeoVideosSetTitleResponse
    */
    public function setTitle($iVideoID, $sVideoTitle) {
        $aArgs = array(
            'video_id' => $iVideoID,
            'title' => $sVideoTitle
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.setTitle', $aArgs);
    }
    
    /**
    * Set a new caption for a video (overwrites previous caption)
    * 
    * @access   public
    * @param    integer     Video ID
    * @param    string      Caption
    * @return   VimeoVideosSetCaptionResponse
    */
    public function setCaption($iVideoID, $sVideoCaption) {
        $aArgs = array(
            'video_id' => $iVideoID,
            'caption' => $sVideoCaption
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.setCaption', $aArgs);
    }
    
    /**
    * Set a video as a favorite.
    * 
    * @access   public
    * @param    integer     Video ID
    * @param    boolean     TRUE to favorite, FALSE to return to normal
    * @return   VimeoVideosSetFavoriteResponse
    */
    public function setFavorite($iVideoID, $bFavorite = true) {
        $aArgs = array(
            'video_id' => $iVideoID,
            'favorite' => (int) $bFavorite
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.setFavorite', $aArgs);
    }
    
    /**
    * Add specified tags to the video, this does not replace any tags.
    * 
    * Tags should be comma separated lists. 
    * 
    * If the calling user is logged in, this will return information that calling
    * user has access to (including private videos). If the calling user is not authenticated,
    * this will only return public information, or a permission denied error if none is available.
    * 
    * @access   public
    * @param    integer     Video ID
    * @param    mixed       Array with tags or Comma separated list of tags ("lions, tigers, bears")
    * @return   VimeoVideosAddTagsResponse
    */
    public function addTags($iVideoID, $mTags) {
        // Catch array of tags
        if(is_array($mTags)) {
            $mTags = implode(',', $mTags);
        }
        
        // Prepare arguments
        $aArgs = array(
            'video_id' => $iVideoID,
            'tags' => $mTags
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.addTags', $aArgs);
    }
    
    /**
    * Remove specified tag from the video.
    * 
    * @access   public
    * @param    integer     Video ID
    * @param    integer     Tag ID, this should be a tag id returned by vimeo.videos.getInfo
    * @return   VimeoVideosRemoveTagResponse
    */
    public function removeTag($iVideoID, $iTagID) {
        $aArgs = array(
            'video_id' => $iVideoID,
            'tag_id' => $iTagID
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.removeTag', $aArgs);
    }
    
    /**
    * Remove ALL of the tags from the video
    * 
    * @access   public
    * @param    integer     Video ID
    * @return   VimeoVideosClearTags 
    */
    public function clearTags($iVideoID) {
        $aArgs = array(
            'video_id' => $iVideoID
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.clearTags', $aArgs);
    }
    
    /**
    * Set the privacy of the video
    * 
    * @access   public
    * @param    integer     Video ID
    * @param    integer     Privacy enum see VimeoVideosRequest::PRIVACY_*
    * @param    mixed       Array or comma separated list of users who can view the video. PRIVACY_USERS must be set.
    */
    public function setPrivacy($iVideoID, $ePrivacy, $mUsers = array()) {
        // Catch array of users
        if(is_array($mUsers)) {
            $mUsers = implode(', ', $mUsers);
        }
        
        $aArgs = array(
            'video_id' => $iVideoID,
            'privacy' => $ePrivacy,
            'users' => $mUsers
        );
        
        return VimeoBase::executeRemoteCall('vimeo.videos.setPrivacy', $aArgs);
    }
}

/**
* Vimeo Videos Search response handler class
*
* Handles the API response for vimeo.videos.search queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/                                                 

class VimeoVideosSearchResponse extends VimeoResponse {
    private $iPage = false;
    private $iItemsPerPage = false;
    private $iOnThisPage = false;
    
    private $aoVideos = array();

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse) {
        parent::__construct($aResponse);
        
        // Parse information
        if($aResponse && isset($aResponse->videos) && $this->getStatus()) {
            // Create an video list instance
            $this->aoVideos = new VimeoVideoList();
            
            // Page information
            $this->iPage = $aResponse->videos->page;
            $this->iItemsPerPage = $aResponse->videos->perpage;
            $this->iOnThisPage = $aResponse->videos->on_this_page;
            
            // Parse videos
            if(isset($aResponse->videos->video)) {
            	// We should check if the subelement is an object (single hit) or an result array (multiple hits)
            	if(is_array($aResponse->videos->video)) {
					// We got a couple of results
					$aParseableData = $aResponse->videos->video;
            	} else {
            		// We only got one result
					$aParseableData = array(
						0 => $aResponse->videos->video
					);
            	}

            	// Parse the results
                foreach($aParseableData as $aVideoInformation) {
                    $oVideo = new VimeoVideoEntity($aVideoInformation);

                    $this->aoVideos->add($oVideo, $oVideo->getID());
                }
            }
        }
    }
    
    /**
    * Current page
    * 
    * @access   public
    * @return   integer     Page number
    */
    public function getPage() {
        return $this->iPage;
    }
    
    /**
    * Items per page
    * 
    * @access   public
    * @return   integer     Items per page
    */
    
    public function getItemsPerPage() {
        return $this->iItemsPerPage;
    }
    
    /**
    * Items on the current page
    * 
    * @access   public
    * @return   integer     Items on the current page
    */
    
    public function getOnThisPage() {
        return $this->iOnThisPage;
    }
    
    /**
    * Get array of video objects
    * 
    * @access   public
    * @return   array       Video objects
    */
    
    public function getVideos() {
        return $this->aoVideos;
    }
}

/**
* Vimeo Videos Search exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.search queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosSearchException extends VimeoException {}

/**
* Vimeo Videos GetList response handler class
*
* Handles the API response for vimeo.videos.getList queries.
* Currently the response is exact the same as vimeo.videos.search
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosGetListResponse extends VimeoVideosSearchResponse {}

/**
* Vimeo Videos Search exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.search queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetListException extends VimeoException {}

/**
* Vimeo Videos GetUploadedList response handler class
*
* Handles the API response for vimeo.videos.getUploadedList queries.
* Currently the response is exact the same as vimeo.videos.search
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosGetUploadedListResponse extends VimeoVideosSearchResponse {}

/**
* Vimeo Videos GetUploadedList exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.getUploadedList queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetUploadedListException extends VimeoException {}

/**
* Vimeo Videos GetAppearsInList response handler class
*
* Handles the API response for vimeo.videos.getAppearsInList queries.
* Currently the response is exact the same as vimeo.videos.search
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosGetAppearsInListResponse extends VimeoVideosSearchResponse {}

/**
* Vimeo Videos GetAppearsInList exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.getAppearsInList queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetAppearsInListException extends VimeoException {}

/**
* Vimeo Videos GetSubscriptionsList response handler class
*
* Handles the API response for vimeo.videos.getSubscriptionsList queries.
* Currently the response is exact the same as vimeo.videos.search
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosGetSubscriptionsListResponse extends VimeoVideosSearchResponse {}

/**
* Vimeo Videos GetSubscriptionsList exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.getSubscriptionsList queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetSubscriptionsListException extends VimeoException {}

/**
* Vimeo Videos GetListByTag response handler class
*
* Handles the API response for vimeo.videos.getListByTag queries.
* Currently the response is exact the same as vimeo.videos.search
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosGetListByTagResponse extends VimeoVideosSearchResponse {}

/**
* Vimeo Videos GetListByTag exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.getListByTag queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetListByTagException extends VimeoException {}

/**
* Vimeo Videos GetLikeList response handler class
*
* Handles the API response for vimeo.videos.getLikeList queries.
* Currently the response is exact the same as vimeo.videos.search
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosGetLikeListResponse extends VimeoVideosSearchResponse {}

/**
* Vimeo Videos GetLikeList exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.getLikeList queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetLikeListException extends VimeoException {}

/**
* Vimeo Videos GetContactsList response handler class
*
* Handles the API response for vimeo.videos.getContactsList queries.
* Currently the response is exact the same as vimeo.videos.search
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosGetContactsListResponse extends VimeoVideosSearchResponse {}

/**
* Vimeo Videos GetContactsList exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.getContactsList queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetContactsListException extends VimeoException {}

/**
* Vimeo Videos getContactsLikeList response handler class
*
* Handles the API response for vimeo.videos.getContactsLikeList queries.
* Currently the response is exact the same as vimeo.videos.search
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosgetContactsLikeListResponse extends VimeoVideosSearchResponse {}

/**
* Vimeo Videos getContactsLikeList exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.getContactsLikeList queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetContactsLikeListException extends VimeoException {}

/**
* Vimeo Videos GetInfo response handler class
*
* Handles the API response for vimeo.videos.getInfo queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosGetInfoResponse extends VimeoResponse {
    private $oVideo = false;

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse) {
        parent::__construct($aResponse);
        
        $this->oVideo = new VimeoVideoEntity($aResponse->video);
    }
    
    /**
    * Get video information as object
    * 
    * @access   public
    * @return   VimeoVideoEntity
    */
    public function getVideo() {
        return $this->oVideo;
    }
}

/**
* Vimeo Videos GetInfo exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.getInfo queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetInfoException extends VimeoException {}

/**
* Vimeo Videos getUploadTicket response handler class
*
* Handles the API response for vimeo.videos.getUploadTicket queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosGetUploadTicketResponse extends VimeoResponse {
    private $sTicket = false;

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse = false) {
        parent::__construct($aResponse);
        
        $this->sTicket = $aResponse->ticket->id;
    }
    
    /**
    * Get generated upload ticket
    * 
    * @access   public
    * @return   string      The ticket number of the upload
    */
    public function getTicket() {
        return $this->sTicket;
    }
}

/**
* Vimeo Videos getUploadTicket exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.getUploadTicket queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosGetUploadTicketException extends VimeoException {}

/**
* Vimeo Videos checkUploadStatus response handler class
*
* Handles the API response for vimeo.videos.checkUploadStatus queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosCheckUploadStatusResponse extends VimeoResponse {
    private $sTicket = false;
    private $iVideoID = false;
    private $bIsUploading = false;
    private $bIsTranscoding = false;
    private $iTranscodingProgress = false;

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse = false) {
        parent::__construct($aResponse);
        
        $this->sTicket = $aResponse->ticket->id;
        $this->iVideoID = $aResponse->ticket->video_id;
        $this->bIsUploading = (bool) $aResponse->ticket->is_uploading;
        $this->bIsTranscoding = (bool) $aResponse->ticket->is_transcoding;
        $this->iTranscodingProgress = $aResponse->ticket->transcoding_progress;
    } 
    
    /**
    * Get Ticket
    * 
    * @access   public
    * @return   string      Ticket
    */
    public function getTicket() {
        return $this->sTicket;
    }
    
    /**
    * Get Video ID
    * 
    * @access   public
    * @return   integer     Video ID
    */
    public function getVideoID() {
        return $this->iVideoID;
    }
    
    /**
    * Is the video uploading?
    * 
    * @access   public
    * @return   boolean     TRUE if uploading, FALSE if not
    */
    public function isUploading() {
        return $this->bIsUploading;
    }
    
    /**
    * Is the video transcoding?
    * 
    * Also check getTranscodingProgress() for percentage in transcoding
    * 
    * @access   public
    * @return   boolean     TRUE if uploading, FALSE if not
    */
    public function isTranscoding() {
        return $this->bIsTranscoding;
    }
    
    /**
    * Get the transcoding progress
    * 
    * Should only be called if isTranscoding() returns true
    * 
    * @access   public
    * @return   integer     Percentage
    */
    public function getTranscodingProgress() {
        return $this->iTranscodingProgress;
    }
}

/**
* Vimeo Videos checkUploadStatus exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.checkUploadStatus queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosCheckUploadStatusException extends VimeoException {}

/**
* Vimeo Videos delete response handler class
*
* Handles the API response for vimeo.videos.delete queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosDeleteResponse extends VimeoResponse {}

/**
* Vimeo Videos delete exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.delete queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosDeleteException extends VimeoException {}

/**
* Vimeo Videos setTitle response handler class
*
* Handles the API response for vimeo.videos.setTitle queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosSetTitleResponse extends VimeoResponse {}

/**
* Vimeo Videos setTitle exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.setTitle queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosSetTitleException extends VimeoException {}

/**
* Vimeo Videos setCaption response handler class
*
* Handles the API response for vimeo.videos.setCaption queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosSetCaptionResponse extends VimeoResponse {}

/**
* Vimeo Videos setCaption exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.setCaption queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosSetCaptionException extends VimeoException {}

/**
* Vimeo Videos setFavorite response handler class
*
* Handles the API response for vimeo.videos.setFavorite queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosSetFavoriteResponse extends VimeoResponse {}

/**
* Vimeo Videos setFavorite exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.setFavorite queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosSetFavoriteException extends VimeoException {}

/**
* Vimeo Videos addTags response handler class
*
* Handles the API response for vimeo.videos.addTags queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosAddTagsResponse extends VimeoResponse {}

/**
* Vimeo Videos addTags exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.addTags queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosAddTagsException extends VimeoException {}

/**
* Vimeo Videos removeTag response handler class
*
* Handles the API response for vimeo.videos.removeTag queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosRemoveTagResponse extends VimeoResponse {}

/**
* Vimeo Videos removeTag exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.removeTag queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosRemoveTagException extends VimeoException {}

/**
* Vimeo Videos clearTags response handler class
*
* Handles the API response for vimeo.videos.clearTags queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosClearTagsResponse extends VimeoResponse {}

/**
* Vimeo Videos clearTags exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.clearTags queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosClearTagsException extends VimeoException {}

/**
* Vimeo Videos setPrivacy response handler class
*
* Handles the API response for vimeo.videos.setPrivacy queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoVideosSetPrivacyResponse extends VimeoResponse {}

/**
* Vimeo Videos setPrivacy exception handler class
*
* Handles exceptions caused by API response for vimeo.videos.setPrivacy queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoVideosSetPrivacyException extends VimeoException {}

/**
* vimeo.people.* methods
*/

/**
* Vimeo People request handler class
*
* Implements all API queries in the vimeo.people.* category
* 
* @package     SimpleVimeo
* @subpackage  ApiRequest
*/

class VimeoPeopleRequest extends VimeoRequest {
    const TYPE_LIKES        = 'likes';
    const TYPE_APPEARS      = 'appears';
    const TYPE_BOTH         = 'likes,appears';
    
    /**
    * Get a user id and full/display name with a username.  
    * 
    * You shouldn't need this to get the User ID, we allow you to use the
    * username instead of User ID everywhere, it's much nicer that way.
    * 
    * @access   public
    * @param    string      The username to lookup
    * @return   VimeoPeopleFindByUsernameResponse
    */
    public function findByUsername($sUsername) {
        $aArgs = array(
            'username' => $sUsername
        );
        
        return VimeoBase::executeRemoteCall('vimeo.people.findByUserName', $aArgs);
    }
    
    /**
    * Get tons of info about a user.
    * 
    * @access   public
    * @param    integer     The id of the user we want. 
    * @return   VimeoPeopleGetInfoResponse
    */
    public function getInfo($iUserID) {
        $aArgs = array(
            'user_id' => $iUserID
        );
        
        return VimeoBase::executeRemoteCall('vimeo.people.getInfo', $aArgs);
    }
    
    /**
    * Get a user id and full/display name via an Email Address.  
    * 
    * You shouldn't need to use this to get the User ID, we allow you
    * to use the username instead of User ID everywhere, it's much nicer that way.
    * 
    * @access   public
    * @param    string      Email
    * @return   VimeoPeopleFindByEmailResponse
    */
    public function findByEmail($sEmail) {
        $aArgs = array(
            'find_email' => $sEmail
        );
        
        return VimeoBase::executeRemoteCall('vimeo.people.findByEmail', $aArgs);
    }
    
    /**
    * Get a portrait URL for a given user/size
    * 
    * Portraits are square, so you only need to pass one size parameter.
    * Possible sizes are 20, 24, 28, 30, 40, 50, 60, 75, 100, 140, 278 and 300
    * 
    * @access   public
    * @param    string      The username to lookup
    * @param    integer     The size of the portrait you you want. (defaults to 75)
    * @return   VimeoPeopleGetPortraitUrlResponse
    * 
    * @todo Check functionality. Did not work, god knows why
    */
    public function getPortraitUrl($sUser, $iSize = false) {
        $aArgs = array(
            'user' => $sUser
        );
        
        if($iSize) {
            $aArgs['size'] = $iSize;
        }
        
        return VimeoBase::executeRemoteCall('vimeo.people.getPortraitUrl', $aArgs);
    }
    
    /**
    * Add a user as a contact for the authenticated user.
    * 
    * If Jim is authenticated, and the $user is sally. Sally will be Jim's contact. 
    * It won't work the other way around. Depending on Sally's settings, this may 
    * send her an email notifying her that Jim Added her as a contact. 
    * 
    * @access   public
    * @param    string      The user to add. User ID, this can be the ID number (151542) or the username (ted)
    * @return   VimeoPeopleAddContactResponse
    */
    public function addContact($sUser) {
        $aArgs = array(
            'user' => $sUser
        );
        
        return VimeoBase::executeRemoteCall('vimeo.people.addContact', $aArgs);
    }
    
    /**
    * Remove a user as a contact for the authenticated user.
    * 
    * @access   public
    * @param    string      The user to remove. User ID, this can be the ID number (151542) or the username (ted)
    * @return   VimeoPeopleRemoveContactResponse
    */
    public function removeContact($sUser) {
        $aArgs = array(
            'user' => $sUser
        );
        
        return VimeoBase::executeRemoteCall('vimeo.people.removeContact', $aArgs);
    }
    
    /**
    * This tells you how much space the user has remaining for uploads.
    * 
    * We provide info in bytes and kilobytes. It probably makes sense for you to use kilobytes. 
    * 
    * @access   public
    * @return   VimeoPeopleGetUploadStatusResponse
    */
    public function getUploadStatus() {
        return VimeoBase::executeRemoteCall('vimeo.people.getUploadStatus');
    }
    
    /**
    * Subscribe to a user's videos.
    * 
    * Just like on the site, you can subscribe to videos a user "appears" in or "likes." Or both!  
    * This will not remove any subscriptions. So if the user is subscribed to a user for both "likes"
    * and "appears," this will not change anything if you only specify one of them. If you want to
    * remove one, you must call vimeo.people.removeSubscription().
    * 
    * @access   public
    * @param    string      User ID, this can be the ID number (151542) or the username (ted)
    * @param    string      with self::TYPE_LIKES or self::TYPE_APPEARS or self::TYPE_BOTH
    * @return   VimeoPeopleAddSubscriptionResponse
    */
    public function addSubscription($sUser, $eType = self::TYPE_BOTH) {
        $aArgs = array(
            'user' => $sUser,
            'type' => $eType
        );
        
        return VimeoBase::executeRemoteCall('vimeo.people.addSubscription', $aArgs);
    }
    
    /**
    * Unsubscribe to a user's videos.
    * 
    * @access   public
    * @param    string      User ID, this can be the ID number (151542) or the username (ted)
    * @param    string      with self::TYPE_LIKES or self::TYPE_APPEARS or self::TYPE_BOTH
    * @return   VimeoPeopleRemoveSubscriptionResponse
    */
    public function removeSubscription($sUser, $eType = self::TYPE_BOTH) {
        $aArgs = array(
            'user' => $sUser,
            'type' => $eType
        );
        
        return VimeoBase::executeRemoteCall('vimeo.people.removeSubscription', $aArgs);
    }
}

/**
* Vimeo People FindByUserName response handler class
*
* Handles the API response for vimeo.people.findByUserName queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoPeopleFindByUserNameResponse extends VimeoResponse {
    private $oUser = false;

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse = false) {
        parent::__construct($aResponse);
        
        $this->oUser = new VimeoUserEntity($aResponse->user);
    }
    
    /**
    * Get user entity object
    * 
    * @access   public
    * @return   VimeoUserEntity
    */
    public function getUser() {
        return $this->oUser;
    }
}

/**
* Vimeo People FindByUserName exception handler class
*
* Handles exceptions caused by API response for vimeo.people.findByUserName queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoPeopleFindByUserNameException extends VimeoException {}

/**
* Vimeo People FindByEmail response handler class
*
* Handles the API response for vimeo.people.findByEmail queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoPeopleFindByEmailResponse extends VimeoResponse {
    private $oUser = false;

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse = false) {
        parent::__construct($aResponse);
        
        $this->oUser = new VimeoUserEntity($aResponse->user);
    }

    /**
    * Get user entity object
    * 
    * @access   public
    * @return   VimeoUserEntity
    */
    public function getUser() {
        return $this->oUser;
    }
}

/**
* Vimeo People FindByEmail exception handler class
*
* Handles exceptions caused by API response for vimeo.people.findByEmail queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoPeopleFindByEmailException extends VimeoException {}

/**
* Vimeo People GetInfo response handler class
*
* Handles the API response for vimeo.people.getInfo queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoPeopleGetInfoResponse extends VimeoResponse {
    private $oUser = false;

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse = false) {
        parent::__construct($aResponse);
        
        $this->oUser = new VimeoUserEntity($aResponse->person);
    }

    /**
    * Get user entity object
    * 
    * @access   public
    * @return   VimeoUserEntity
    */
    public function getUser() {
        return $this->oUser;
    }
}

/**
* Vimeo People GetInfo exception handler class
*
* Handles exceptions caused by API response for vimeo.people.getInfo queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoPeopleGetInfoException extends VimeoException {}

/**
* Vimeo People getPortraitUrl response handler class
*
* Handles the API response for vimeo.people.getPortraitUrl queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoPeopleGetPortraitUrlResponse extends VimeoResponse {}

/**
* Vimeo People getPortraitUrl exception handler class
*
* Handles exceptions caused by API response for vimeo.people.getPortraitUrl queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoPeopleGetPortraitUrlException extends VimeoException {}

/**
* Vimeo People addContact response handler class
*
* Handles the API response for vimeo.people.addContact queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoPeopleAddContactResponse extends VimeoResponse {}

/**
* Vimeo People addContact exception handler class
*
* Handles exceptions caused by API response for vimeo.people.addContact queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoPeopleAddContactException extends VimeoException {}

/**
* Vimeo People removeContact response handler class
*
* Handles the API response for vimeo.people.removeContact queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoPeopleRemoveContactResponse extends VimeoResponse {}

/**
* Vimeo People removeContact exception handler class
*
* Handles exceptions caused by API response for vimeo.people.removeContact queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoPeopleRemoveContactException extends VimeoException {}

/**
* Vimeo People getUploadStatus response handler class
*
* Handles the API response for vimeo.people.getUploadStatus queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoPeopleGetUploadStatusResponse extends VimeoResponse {
    private $iMaxBytes = false;
    private $iMaxKBytes = false;
    
    private $iUsedBytes = false;
    private $iUsedKBytes = false;
    
    private $iRemainingBytes = false;
    private $iRemainingKBytes = false;

    /**
    * Constructor
    * 
    * Parses the API response
    * 
    * @access   public
    * @param    stdClass    API response
    * @return   void
    */
    public function __construct($aResponse = false) {
        parent::__construct($aResponse);
        
        $this->iMaxBytes = $aResponse->user->bandwidth->maxbytes;
        $this->iMaxKBytes = $aResponse->user->bandwidth->maxkb;
        
        $this->iUsedBytes = $aResponse->user->bandwidth->usedbytes;
        $this->iUsedKBytes = $aResponse->user->bandwidth->usedkb;
        
        $this->iRemainingBytes = $aResponse->user->bandwidth->remainingbytes;
        $this->iRemainingKBytes = $aResponse->user->bandwidth->remainingkb;
    }
    
    /**
    * Get maximum upload for this week in BYTES
    * 
    * @access   public
    * @return   integer     Maximum bytes this week
    */
    public function getMaxBytes() {
        return $this->iMaxBytes;
    }
    
    /**
    * Get maximum upload for this week in KILOBYTES
    * 
    * @access   public
    * @return   integer     Maximum kbytes this week
    */
    public function getMaxKiloBytes() {
        return $this->iMaxKBytes;
    }
    
    /**
    * Get used upload for this week in BYTES
    * 
    * @access   public
    * @return   integer     Used bytes this week
    */
    public function getUsedBytes() {
        return $this->iUsedBytes;
    }
    
    /**
    * Get used upload for this week in KILOBYTES
    * 
    * @access   public
    * @return   integer     Used kbytes this week
    */
    public function getUsedKiloBytes() {
        return $this->iUsedKBytes;
    }
    
    /**
    * Get remaining upload for this week in BYTES
    * 
    * @access   public
    * @return   integer     Remaining bytes this week
    */
    public function getRemainingBytes() {
        return $this->iRemainingBytes;
    }
    
    /**
    * Get remaining upload for this week in KILOBYTES
    * 
    * @access   public
    * @return   integer     Remaining kbytes this week
    */
    public function getRemainingKiloBytes() {
        return $this->iRemainingKBytes;
    }
}

/**
* Vimeo People getUploadStatus exception handler class
*
* Handles exceptions caused by API response for vimeo.people.getUploadStatus queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoPeopleGetUploadStatusException extends VimeoException {}

/**
* Vimeo People addSubscription response handler class
*
* Handles the API response for vimeo.people.addSubscription queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoPeopleAddSubscriptionResponse extends VimeoResponse {}

/**
* Vimeo People addSubscription exception handler class
*
* Handles exceptions caused by API response for vimeo.people.addSubscription queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoPeopleAddSubscriptionException extends VimeoException {}

/**
* Vimeo People removeSubscription response handler class
*
* Handles the API response for vimeo.people.removeSubscription queries.
* 
* @package     SimpleVimeo
* @subpackage  ApiResponse
*/

class VimeoPeopleRemoveSubscriptionResponse extends VimeoResponse {}

/**
* Vimeo People removeSubscription exception handler class
*
* Handles exceptions caused by API response for vimeo.people.removeSubscription queries.
* 
* @package     SimpleVimeo
* @subpackage  Exceptions
*/

class VimeoPeopleRemoveSubscriptionException extends VimeoException {}

?>