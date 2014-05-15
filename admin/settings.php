<?php
/**
 * Store settings related to the plugin
 *
 */
class Inf_Member_Settings {

    /**
     * All plugin features supported
     *
     * @var array {
     *     @type string feature slug
     *     @type bool true
     * }
     */
    public static $features = array( 'docs' => true, 'log' => true, 'tests' => true, 'members' => true, 'options' => true );

	/**
	 * Add hooks
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( 'Inf_Member_Settings', 'settings_menu_items' ) );
		add_action( 'admin_enqueue_scripts', array( 'Inf_Member_Settings', 'enqueue_scripts' ) );
	}


	/**
	 * Enqueue scripts and styles.
	 *
	 * @uses wp_enqueue_style()
	 * @return void
	 */
	public static function enqueue_scripts() {
		//wp_enqueue_style( 'facebook-admin-icons', plugins_url( 'static/css/admin/icons' . ( ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min' ) . '.css', dirname( __FILE__ ) ), array(), '1.5' );
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
	 * Add Inf_Member settings to the WordPress administration menu.
	 *
	 * @global Inf_Member $inf Access loaded api credentials
	 * @global $submenu array submenu created for the menu slugs
	 * @return void
	 */
	public static function settings_menu_items() {
		global $inf, $submenu;

		// main settings page
		if ( ! class_exists( 'Inf_Member_App_Settings' ) )
			require_once( dirname( __FILE__ ) . '/settings-app.php' );

		$menu_hook = Inf_Member_App_Settings::menu_item();
		if ( ! $menu_hook )
			return;

		$app_credentials_exist = self::app_credentials_exist();

		$menu_slug = Inf_Member_App_Settings::PAGE_SLUG;

        // duplicate_hook
        $available_features = apply_filters( 'inf_mem_features', self::$features );

        // could short-circuit all features
        if ( ! is_array( $available_features ) || empty( $available_features ) )
            return;

        /* //Geo
        if ( isset( $available_features['geo']) && current_user_can(Inf_Member::PERMISSION_ADMIN) && $app_credentials_exist) {
            if ( ! class_exists( 'Inf_Member_App_Settings_Geo' ) )
                require_once( dirname(__FILE__) . '/settings-geo.php' );

            Sellnow_WP_App_Settings_Geo::add_submenu_item( $menu_slug );
        }*/

        // Members
        if ( isset( $available_features['members'] ) ) {
            if ( ! class_exists( 'Inf_Member_Settings_Members' ) )
                require_once( dirname(__FILE__) . '/settings-members.php' );

            Inf_Member_Settings_Members::add_submenu_item( $menu_slug );
        }

        // Options
        if ( isset( $available_features['options'] ) ) {
            if ( ! class_exists( 'Inf_Member_Settings_Options' ) )
                require_once( dirname(__FILE__) . '/settings-options.php' );

            Inf_Member_Settings_Options::add_submenu_item( $menu_slug );
        }

        //Docs
        if( isset($available_features['docs']) ){
            if ( ! class_exists( 'Inf_Member_Settings_Docs' ) )
                require_once( dirname(__FILE__) . '/settings-docs.php' );

            Inf_Member_Settings_Docs::add_submenu_item( $menu_slug );
        }

        //Log
        if ( isset( $available_features['log'] ) ) {
            if ( ! class_exists( 'Inf_Member_Settings_Log' ) )
                require_once( dirname(__FILE__) . '/settings-log.php' );

            Inf_Member_Settings_Log::add_submenu_item( $menu_slug );
        }

        //Tests
        if ( isset( $available_features['tests'] ) ) {
            if ( ! class_exists( 'Inf_Member_Settings_Tests' ) )
                require_once( dirname(__FILE__) . '/settings-tests.php' );

            Inf_Member_Settings_Tests::add_submenu_item( $menu_slug );
        }


        // make an assumption about submenu mappings, but don't fail if our assumption is wrong
        // WordPress will automatically duplicate the top-level menu destination when a submenu is created
        // Change wording based on Facebook parent
        if ( is_array( $submenu ) && isset( $submenu[$menu_slug] ) && is_array( $submenu[$menu_slug] ) && is_array( $submenu[$menu_slug][0] ) && is_string( $submenu[$menu_slug][0][0] ) ) {
            $submenu[$menu_slug][0][0] = __('API Settings');
        }
		
	}

	/**
	 * Standardize the form flow through settings API.
	 *
	 * @uses settings_fields()
	 * @uses do_settings_sections()
	 * @param string $page_slug constructs custom actions. passed to Settings API functions
	 * @param string $page_title placed in a <h2> at the top of the page
	 * @return void
	 */
	public static function settings_page_template( $page_slug, $page_title ) {
		echo '<div class="wrap">';

		/**
		 * Echo content before the page header.
		 */
		do_action( 'inf_mem_settings_before_header_' . $page_slug );
		echo '<header><h2>' . esc_html( $page_title ) . '</h2></header>';
		/**
		 * Echo content after the page header.
		 */
		do_action( 'inf_mem_settings_after_header_' . $page_slug );

		// handle general messages such as settings updated up top
		// place individual settings errors alongside their fields
		settings_errors( 'general' );

		echo '<form method="post" action="options.php">';

		settings_fields( $page_slug );
		do_settings_sections( $page_slug );

		submit_button();
		echo '</form>';
		echo '</div>';

		/**
		 * Echo content at the bottom of the page.
		 */
		do_action( 'inf_mem_settings_footer_' . $page_slug );
	}

	/**
	 * Link to settings from the plugin listing page
	 *
	 *
	 * @param array $links links displayed under the plugin
	 * @param string $file plugin main file path relative to plugin dir
	 * @return array links array passed in, possibly with our settings link added
	 */
	public static function plugin_action_links( $links, $file ) {
		if ( $file === plugin_basename( dirname( dirname(__FILE__) ) . '/inf-member.php' ) ) {
			if ( ! class_exists( 'Inf_Member_App_Settings' ) )
				require_once( dirname( __FILE__ ) . '/settings-app.php' );

			$links[] = '<a href="' . esc_url( admin_url( 'admin.php' ) . '?' . http_build_query( array( 'page' => Inf_Member_App_Settings::PAGE_SLUG ) ) ) . '">' . __( 'Settings' ) . '</a>';
		}

		return $links;
	}

    /**
     * Clean user inputs before saving to database.
     *
     * @ref https://codex.wordpress.org/Function_Reference/sanitize_text_field
     * @param array $options form options values
     * @return array $options sanitized options
     */
    public static function sanitize_options( $options ) {

        // start fresh
        $clean_options = array();
        $error = false;

        foreach($options as $k => $option){
            if(!empty($option)){
                $clean_options[$k] = sanitize_text_field(trim($option));
            }else{
                $error = true;
                add_settings_error( $k, $k.'-error', __( 'Fields can not be empty', 'inf-member' ) );
            }
        }

        if(!$error)
            return $clean_options;
        else
            return array();
    }


}
?>
