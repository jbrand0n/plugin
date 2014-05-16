<?php

/**
 * Display a settings page for application data
 *
 */
class Inf_Member_Settings_Tests {

    /**
     * Settings page identifier.
     *
     * @var string
     */
    const PAGE_SLUG = 'inf-mem-app-tests';

    /**
     * Define our option array value.
     *
     * @var string
     */
    const OPTION_NAME = 'inf_member';

    /**
     * Initialize with an options array.
     *
     * @param array $options existing options
     */
    public function __construct( $options = array() ) {
        if ( is_array( $options ) && ! empty( $options ) )
            $this->existing_options = $options;
        else
            $this->existing_options = array();
    }

    /**
     * Load stored options and scripts on settings page view.
     *
     * @uses get_option() load existing options
     * @return void
     */
    public function onload() {
        $options = get_option( self::OPTION_NAME );
        if ( ! is_array( $options ) )
            $options = array();
        $this->existing_options = $options;


        // notify of lack of HTTPS
        //@todo add DB test if credentials exist
        //if ( ! wp_http_supports( array( 'ssl' => true ) ) )
        //add_action( 'admin_notices', array( 'Sellnow_WP_App_Settings', 'admin_notice' ) );


    }


    /**
     * Reference the plugin by name.
     *
     * @return string plugin name
     */
    public static function social_plugin_name() {
        return __( 'Tests', 'inf_mem' );
    }


    /**
     * Navigate to the settings page through the top-level menu item.
     *
     * @uses add_submenu_page()
     * @param string $parent_slug Facebook top-level menu item slug
     * @return string submenu hook suffix
     */
    public static function add_submenu_item( $parent_slug ) {
        $log = new Inf_Member_Settings_Tests();

        $hook_suffix = add_submenu_page(
            $parent_slug,
            self::social_plugin_name(),
            self::social_plugin_name(),
            'manage_options',
            self::PAGE_SLUG,
            array( &$log, 'content' )
        );

        if ( $hook_suffix ) {
            $log->hook_suffix = $hook_suffix;
            //register_setting( $hook_suffix, self::OPTION_NAME, array( 'Sellnow_WP_App_Settings_Log', 'sanitize_options' ) );
            add_action( 'load-' . $hook_suffix, array( &$log, 'onload' ) );
        }

        return $hook_suffix;
    }


    /**
     * Page content.
     *
     * @global Inf_Member $inf
     * @return void
     */
    public function content() {
        global $inf, $iSDK;

$user = get_user_by( 'email', 'jfwhitton@yahoo.com.au' );
print_r($user);
exit;

        if ( ! class_exists( 'Inf_Member_Settings' ) )
            require_once( dirname(__FILE__) . '/settings.php' );

        echo '<div class="wrap">';
        echo '<header><h2>' . esc_html( self::social_plugin_name() ) . '</h2></header>';


        echo '</div>';


        $inf->load_php_sdk();

        echo '<h1>Inf: </h1><br>'.print_r($iSDK,true);
        echo '<br><br><hr /><br><br>';

        /*$returnFields = array('Id', 'FirstName', 'LastName');
        $data = $iSDK->contact('findByEmail', 'test@test.com',$returnFields);
        print_r($data);*/
		
		$returnFields = array('GroupId','ContactGroup','ContactId');
		$query = array('ContactGroup' => 'Order Sheet EA Sponsored');
		//$query = array('GroupId' => '1814');
        $data = $iSDK->Data('query',"ContactGroupAssign",1000,0,$query,$returnFields);
		//echo '<pre>';
        //print_r($data);
        //echo '</pre>';
		
		
		
		foreach($data as $key=>$val){
			$returnFields = array('Email','Firstname','LastName');
			$query = array('Id' => $val['ContactId']);
			$data1 = $iSDK->Data('query',"Contact",1,0,$query,$returnFields);
			//echo $val['ContactId'];
			print_r($data1);
			$user_id = wp_create_user( $data1[0]['Email'], 'test', $data1[0]['Email'] );
			wp_update_user(
					array(
					  'ID'          =>    $user_id,
					  'nickname'    =>    $data1[0]['Firstname']
					)
				  );

			exit;
		}
		exit;
		
		$results = $app->dsQuery('ContactGroupAssign', 1000, 0, array('GroupId' => $tag), array('Contact.FirstName', 'Contact.LastName', 'Contact.City','Contact.State','Contact.PostalCode','Contact.Phone1','Contact.Website','Contact.Email', 'Contact.Groups','ContactId'));
		
		
		$returnFields = array('Id');
		$query = array('GroupName' => 'platinum-upgrade');
        $data = $iSDK->Data('query',"ContactGroup",1000,0,$query,$returnFields);
        echo '<pre>';
        print_r($data);
        echo '</pre>';

    }
}