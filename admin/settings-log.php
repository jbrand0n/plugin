<?php

/**
 * Display a settings page for application data
 *
 */
class Inf_Member_Settings_Log {

    /**
     * Settings page identifier.
     *
     * @var string
     */
    const PAGE_SLUG = 'inf-mem-app-log';

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
        return __( 'Log', 'inf_mem' );
    }


    /**
     * Navigate to the settings page through the top-level menu item.
     *
     * @uses add_submenu_page()
     * @param string $parent_slug Facebook top-level menu item slug
     * @return string submenu hook suffix
    */
    public static function add_submenu_item( $parent_slug ) {
        $log = new Inf_Member_Settings_Log();

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
    public static function content() {
        global $inf;

        if ( ! class_exists( 'Inf_Member_Settings' ) )
            require_once( dirname(__FILE__) . '/settings.php' );

        echo '<div class="wrap">';
        echo '<header><h2>' . esc_html( self::social_plugin_name() ) . '</h2></header>';

        self::server_info();
        self::show_log();

        echo '</div>';


    }

    /**
     * Read Log File
     *
     * @return void
     */
    public static function show_log() {
        $file = dirname(dirname(__FILE__)).'/log/info.log';
        $log = file_get_contents($file);
        echo '<section id="debug-log"><header><h3>' . esc_html( __( 'Log View', 'sellnow' ) ) . '</h3></header>';
        echo "<textarea rows='50' cols='100'>$log</textarea>";
        echo '</section>';

    }


    /**
     * How does the site communicate with Facebook?
     *
     * @return void
     */
    public static function server_info() {
        echo '<section id="debug-server"><header><h3>' . esc_html( __( 'Server configuration', 'sellnow' ) ) . '</h3></header><table><thead><th>' . esc_html( __( 'Feature', 'facebook' ) ) . '</th><th>' . esc_html( _x( 'Info', 'Information', 'facebook' ) ) . '</th></thead><tbody>';

        // PHP version
        echo '<tr><th>' . esc_html( sprintf( _x( '%s version', 'software version', 'sellnow' ), 'PHP' ) ) . '</th><td>';
        // PHP > 5.2.7
        if ( defined( 'PHP_MAJOR_VERSION' ) && defined( 'PHP_MINOR_VERSION' ) && defined( 'PHP_RELEASE_VERSION' ) )
            echo esc_html( PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION );
        else
            esc_html( phpversion() );
        echo '</td></tr>';

        // WordPress version
        echo '<tr><th>' . esc_html( sprintf( _x( '%s version', 'software version', 'facebook' ), 'WordPress' ) ) . '</th><td>' . esc_html( get_bloginfo( 'version' ) ) . '</td></tr>';

        if ( isset( $_SERVER['SERVER_SOFTWARE'] ) )
            echo '<tr><th>' . esc_html( __( 'Server software', 'facebook' ) ) . '</th><td>' . esc_html( $_SERVER['SERVER_SOFTWARE'] ) . '</td></tr>';

        // WP_HTTP connection for SSL
        echo '<tr id="debug-server-http">';
        echo '<th>' . sprintf( esc_html( _x( '%s connection method', 'server-to-server connection', 'facebook' ) ), '<a href="http://codex.wordpress.org/HTTP_API">WP_HTTP</a>' ) . '</th><td>';
        $http_obj = _wp_http_get_object();
        $http_transport = $http_obj->_get_first_available_transport( array( 'ssl' => true ) );
        if ( is_string( $http_transport ) && strlen( $http_transport ) > 8 ) {
            $http_transport = strtolower( substr( $http_transport, 8 ) );
            if ( $http_transport === 'curl' ) {
                echo '<a href="http://php.net/manual/book.curl.php">cURL</a>';
                $curl_version = curl_version();
                if ( isset( $curl_version['version'] ) )
                    echo ' ' . esc_html( $curl_version['version'] );
                if ( isset( $curl_version['ssl_version'] ) ) {
                    echo '; ';
                    $ssl_version = $curl_version['ssl_version'];
                    if ( strlen( $curl_version['ssl_version'] ) > 8 && substr_compare( $ssl_version, 'OpenSSL/', 0, 8 ) === 0 )
                        echo '<a href="http://openssl.org/">OpenSSL</a>/' . esc_html( substr( $ssl_version, 8 ) );
                    else
                        echo esc_html( $ssl_version );
                    unset( $ssl_version );
                }
                unset( $curl_version );
            } else if ( $http_transport === 'streams' ) {
                echo '<a href="http://www.php.net/manual/book.stream.php">Stream</a>';
            } else if ( $http_transport === 'fsockopen' ) {
                echo '<a href="http://php.net/manual/function.fsockopen.php">fsockopen</a>';
            } else {
                echo $http_transport;
            }
        } else {
            echo _x( 'none available', 'No available solution found.', 'sellnow' );
        }
        echo '</td></tr>';
        unset( $http_transport );
        unset( $http_obj );

        echo '</table></section>';
    }


}