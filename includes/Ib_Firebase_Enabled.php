<?php
namespace Ibfe;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Wp_User;

class Ib_Firebase_Enabled{
	const PARAM_AUTH_METHOD = 'ibfe_auth_method';
	const PARAM_ACCESS_TOKEN = 'access_token';
	const METHOD_FACEBOOK = 'facebook';

    public static function init() {
        // 1) firebase pass params access_token and email to wp in POST
        // 2) use wp action as the point to capture the POST inputs
        // 3) authenticate or register them
        // 4) redirect them to wp home
        add_action('wp', array(static::class, 'auth'));
    }

    public static function auth() {
    	$method = request()->input(static::PARAM_AUTH_METHOD, static::METHOD_FACEBOOK);

    	switch($method) {
		    default:
		    	static::authWithFacebook();
		    	break;
	    }
    }

    public static function authWithFacebook() {
    	$access_token = request()->input(static::PARAM_ACCESS_TOKEN);

    	$fb = $response = $fbUser = $userID = $errorMessage = null;

    	// Initialize the SDK
	    try {
		    $fb = new Facebook( [
			    'app_id'                => FB_APP_ID,
			    'app_secret'            => FB_APP_SECRET,
			    'default_graph_version' => 'v3.1',
		    ] );
	    } catch ( FacebookSDKException $e ) {
	    }

	    if(!$fb) return false;

	    // Request data with the token
	    try {
		    // Returns a `Facebook\FacebookResponse` object
		    $response = $fb->get('/me?fields=id,name,email', "{$access_token}");
	    } catch( FacebookResponseException $e) {
		    $errorMessage = $e->getMessage();
	    } catch( FacebookSDKException $e) {
		    $errorMessage = $e->getMessage();
	    }

	    if(!$response) return false;

	    // Getting the facebook user
	    try {
		    $fbUser = $response->getGraphUser();
	    } catch ( FacebookSDKException $e ) {
		    $errorMessage = $e->getMessage();
	    }

	    if(!$fbUser) return false;

	    $fbUserEmail = $fbUser->getEmail();

	    // Get user with the email and get the ID
	    if($user = get_user_by_email(
	    	$fbUserEmail
	    )) {
	    	$userID = $user->ID;

	    // User with the email not exist, then register them
	    } else {
	    	if($result = wp_create_user($fbUserEmail, $access_token, $fbUserEmail))
	    		if(!is_wp_error($result)) {
				    // Pass the user ID for next step
	    			$userID = $result;
			    } else {
				    $errorMessage = $result->get_error_message();
			    }
	    }

	    // User with the email already exist, then signin them
	    if($userID) {
		    wp_set_auth_cookie($userID);

		    // Redirect to home
		    wp_safe_redirect(
		    	home_url('/')
		    );
		    exit;
	    } else {
	    	// Redirect to login page, if this request not complete
	    	if(!empty($access_token)) {
	    		wp_safe_redirect(
	    			wp_login_url() . "?error-message={$errorMessage}"
			    );
	    		exit;
		    }
	    }
    }
}