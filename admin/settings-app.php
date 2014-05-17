<?php

/**
 * Display a settings page for application data
 *
 */
class Inf_Member_App_Settings {
	
	/**
	 * Settings page identifier.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'inf-member-app-settings';

	/**
	 * Define our option array value.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'inf_member';


	/**
	 * The hook suffix assigned by add_submenu_page()
	 *
	 * @var string
	 */
	protected $hook_suffix = '';

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
	 * Add a menu item to WordPress admin.
	 *
	 * @uses add_utility_page()
	 * @return string page hook
	 */
	public static function menu_item() {
		$app_settings = new Inf_Member_App_Settings();

		$hook_suffix = add_utility_page(
			__( 'Inf Member Plugin Settings', 'inf-member' ), // page <title>
			'Inf Member', // menu title
			'manage_options', // capability needed
			self::PAGE_SLUG, // what should I call you?
			array( &$app_settings, 'settings_page' ), // pageload callback
			'' // to be replaced by dashicon
		);

		// conditional load CSS, scripts
		if ( $hook_suffix ) {
			$app_settings->hook_suffix = $hook_suffix;
			register_setting( $hook_suffix, self::OPTION_NAME, array( 'Inf_Member_App_Settings', 'sanitize_options' ) );
			add_action( 'load-' . $hook_suffix, array( &$app_settings, 'onload' ) );
		}

		return $hook_suffix;
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
		if ( ! wp_http_supports( array( 'ssl' => true ) ) )
			add_action( 'admin_notices', array( 'Inf_Member_App_Settings', 'admin_notice' ) );

		$this->settings_api_init();

		//add_action( 'admin_enqueue_scripts', array( 'Inf_Member_App_Settings', 'enqueue_scripts' ) );
	}

	/**
	 * Warn of minimum requirements not met for app access token.
	 *
	 * @return void
	 */
	public static function admin_notice() {
		echo '<div class="error">';
		echo '<p>' . esc_html( __( 'Your server does not support communication with servers over HTTPS.', 'inf-member' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Load the settings page.
	 *
	 * @return void
	 */
	public function settings_page() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		add_action( 'inf_member_settings_after_header_' . $this->hook_suffix, array( 'Inf_Member_App_Settings', 'after_header' ) );

        Inf_Member_Settings::settings_page_template( $this->hook_suffix, __( 'Inf Member Settings', 'inf-member' ) );
	}


	/**
	 * Optional Content after header.
	 *
	 * @return void
	 */
	public static function after_header() {

	}

	/**
	 * Hook into the settings API.
	 *
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 * @return void
	 */
	private function settings_api_init() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		// Infusion API settings
		$section = 'infusion-api';
		add_settings_section(
			$section,
			__( 'Infusion API Settings', 'inf-member' ),
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);

		add_settings_field(
			'inf_url',
			_x( 'Infusion API Service URL', 'inf-member' ),
			array( &$this, 'display_inf_url' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'inf_url' )
		);
		add_settings_field(
			'inf_key',
			_x( 'Infusion API key', 'inf-member' ),
			array( &$this, 'display_api_key' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'inf_key' )
		);

        /*if(Inf_Member::app_credentials_exist())
            $this->db_install();*/
	}

	/**
	 * Introduction to the application settings section.
	 *
	 * @return void
	 */
	public function section_header() {
		echo '<p>Other menu options will only show when there is a connection to the API.</p>';
	}


	/**
	 * Display the Infusion URL input field.
	 *
	 * @return void
	 */
	public function display_inf_url() {
		$key = 'inf_url';

		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		$id = $key;
		settings_errors( $id );
		echo 'http://<input type="text" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $id . '"';
		if ( $existing_value )
			echo ' value="' . esc_attr( $existing_value ) . '"';
		echo ' maxlength="32" size="40" pattern="[a-zA-Z0-9-_]+" autocomplete="off" />.infusionsoft.com';

		echo '<p class="description">' . esc_html( __( 'Your subdomain for Infusion login', 'inf-mem' ) ) . '</p>';
	}

	/**
	 * Display the Infusion api key input field.
	 *
	 * @return void
	 */
	public function display_api_key() {
		$key = 'inf_key';

		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		$id = $key;
		settings_errors( $id );
		echo '<input type="password" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $id . '"';
		if ( $existing_value )
			echo ' value="' . esc_attr( $existing_value ) . '"';
		echo ' size="40" autocomplete="off" pattern="[a-zA-Z0-9-_]+" />';

		echo '<p class="description">' .  __( 'Not sure what the API key is or where to get it? <a target="_blank" href="http://kb.infusionsoft.com/index.php?/article/AA-00442/0/How-do-I-enable-the-Infusionsoft-API.html">Click here</a>', 'fb-eventpresso' ) . '</p>';
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

    /**
     * Start DB update Process
     *
     */
    public function db_install(){

        if ( !class_exists( 'Inf_Member_Upgrade') )
            require_once( dirname(dirname(__FILE__)) . '/includes/db/upgrade.php' );

        $upd = new Inf_Member_Upgrade;
        $upd->init();

        if(isset($_GET['update_db']) && $_GET['update_db'])
            $upd->run_updates();

    }


}

?>
