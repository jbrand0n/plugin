<?php
/**
 * @package Inf-Member
 * @version 0.1.0
 */
/*
Plugin Name: Infusion Member WP Integration
Plugin URI: 
Description: A lightweight but powerful Membership system with Infusion
Author: Inf member
Author URI: 
Version: 0.1.0
*/

/**
 * Loads the Inf-Member plugin.
 *
 * Load the Inf-Member plugin for WordPress based on an access method of admin or public site.
 *
 */
class Inf_Member {

    /**
	 * Uniquely identify plugin version
	 * Bust caches based on this value
	 *
	 * @var string
	 */
	const VERSION = '0.1.0';

    /**
     * Define our option array value.
     *
     * @var string
     */
    const OPTION_NAME = 'inf_member';

	/**
	 * Store Infusion API information (id, secret, namespace, access token, appsecret proof) if available.
	 */
	public $credentials = array();



	/**
	 * Configures the plugin and future actions.
	 *
	 */
	public function __construct() {
		// load plugin files relative to this directory
		$this->plugin_directory = dirname(__FILE__) . '/';

		// load API data
		$credentials = get_option( self::OPTION_NAME );
        if ( ! is_array( $credentials ) )
			$credentials = array();
		$this->credentials = $credentials;
		unset( $credentials );

        //add last login timestamp
        add_action('wp_login',array(&$this, 'last_login'));


		if ( is_admin() ) {
			//add_action( 'admin_enqueue_scripts', array( &$this, 'register_js_sdk' ), 1 );
			$this->admin_init();
		} else {
			//add_action( 'wp_enqueue_scripts', array( &$this, 'register_js_sdk' ), 1 );

			// split initialization functions into early (init) and regular (wp) action groups			

			add_action( 'wp', array( &$this, 'public_init' ) );
		}	


		// load shortcodes
		/*
		if ( ! class_exists( 'Facebook_Shortcodes' ) )
			require_once( $this->plugin_directory . 'social-plugins/shortcodes.php' );
		Facebook_Shortcodes::init();
		*/
		
	}

    /**
     * Intialize the public, front end views
     *
     * @global Inf_Member $inf
     * @return void
     */
    public function public_init() {
        global $inf, $inf_front;

        // Load Infusion DB
        if( !isset($iMemDb) )
            $this->load_mem_db();
        
        //Frontend functions
        if ( ! class_exists( 'Inf_Member_Front' ) )
            require_once( $this->plugin_directory . 'inf-front.php' );

        $inf_front = new Inf_Member_Front;

    }

	/**
	 * Initialize the backend, administrative views.
	 *
	 * @return void
	 */
	public function admin_init() {
		global $iMemDb, $inf_admin;

        $admin_dir = $this->plugin_directory . 'admin/';

		// InfMember settings loader
		if ( ! class_exists( 'Inf_Member_Settings' ) )
			require_once( $admin_dir . 'settings.php' );
        Inf_Member_Settings::init();

        // Load Infusion DB
        if( !isset($iMemDb) )
            $this->load_mem_db();

        //Admin functions
        if ( ! class_exists( 'Inf_Member_Admin' ) )
            require_once( $admin_dir . 'admin.php' );

         $inf_admin = new Inf_Member_Admin;

	}


    /**
     * Check if application credentials are stored for the current site.
     *
     * Limit displayed features based on the existence of app data.
     *
     * @global Inf_Member $inf access already requested api credentials
     * @return bool True if app_id and app_secret stored.
     */
    public static function app_credentials_exist() {
        global $inf;

        if ( isset( $inf ) && isset( $inf->credentials ) && isset($inf->credentials['inf_url']) && isset($inf->credentials['inf_key']) )
            return true;

        return false;
    }

	/**
	 * Initialize a global $iSDK variable if one does not already exist and credentials stored for this site.
	 *
	 * @global Inf_Member $iSDK existing Infusion SDK for PHP instance
	 * @return true if $iSDK global exists, else false
	 */
	public function load_php_sdk() {
		global $iSDK;

		if ( isset( $iSDK ) )
			return true;

		$get_iSDK = $this->get_php_sdk();

        if ( $get_iSDK ) {
			$iSDK = $get_iSDK;
			return true;
		}

		return false;
	}

	/**
	 * Initialize the Infsuion PHP SDK using an application identifier and secret.
	 *
	 * @return Inf_Member Infusion SDK for PHP class or null if minimum requirements not met
	 */
	public function get_php_sdk() {

        if ( !$this->app_credentials_exist() )
			return;

		// Infusion SDK for PHP
		if ( !class_exists( 'Infusionsoft') )
			require_once( $this->plugin_directory . 'includes/infusionsoft.php' );

        $in = new Infusionsoft( $this->credentials['inf_url'], $this->credentials['inf_key'] );

        if ( is_wp_error($in->error) )
            echo '<div id="message" class="error"><p>'.$in->error->get_error_message().'</p></div>';
        else
            return $in;

	}

    /**
     * Initialize a global $iMemDb variable if one does not already exist and credentials stored for this site.
     *
     * @global Inf_Member $iMemDb
     * @return true if $iMemDb global exists, else false
     */
    public function load_mem_db(){
        global $iMemDb;

        if( isset($iMemDb) )
            return true;

        $get_iMemDb = $this->get_mem_db();

        if ( $get_iMemDb ) {
            $iMemDb = $get_iMemDb;
            return true;
        }

        return false;

    }

    /**
     * Initialize the Infusion Member DB
     *
     * @return Inf_Member Infusion Member DB class or null if minimum requirements not met
     */
    public function get_mem_db() {

        if ( !$this->app_credentials_exist() )
            return;

        // Infusion SDK for PHP
        if ( !class_exists( 'Inf_Member_DB') )
            require_once( $this->plugin_directory . 'includes/Inf_Member_DB.php' );

        $in = new Inf_Member_DB();

        if ( is_wp_error($in->error) )
            echo '<div id="message" class="error"><p>'.$in->error->get_error_message().'</p></div>';
        else
            return $in;

    }

    /**
     * Add last_login to user_meta
     *
     * @ref https://codex.wordpress.org/Function_Reference/update_user_meta
     * @uses $user_ID
     */
    public static function last_login(){
        global $user_ID;
        update_user_meta( $user_ID, 'last_login', time() );
        add_user_meta( $user_ID, 'level_of_awesomeness', 111);
    }


} //class end


/**
 * Load plugin function during the WordPress init action
 *
 * @return void
 */
function inf_member_init() {
	global $inf;

	$inf = new Inf_Member();
}
add_action( 'init', 'inf_member_init', 0 ); // load before widgets_init at 1