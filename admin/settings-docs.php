<?php

/**
 * Display a settings page for application data
 *
 */
class Inf_Member_Settings_Docs {

    /**
     * Settings page identifier.
     *
     * @var string
     */
    const PAGE_SLUG = 'inf-mem-app-docs';

    /**
     * Define our option array value.
     *
     * @var string
     */
    const OPTION_NAME = 'inf_member';

    public $markdown;

    /**
     * Initialize with an options array.
     *
     * @param array $options existing options
     */
    public function __construct( $options = array() ) {

        $this->plugin_directory = dirname(dirname(__FILE__)) . '/';

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

        $this->get_markdown();

        // notify of lack of HTTPS
        //@todo add DB test if credentials exist
        //if ( ! wp_http_supports( array( 'ssl' => true ) ) )
        //add_action( 'admin_notices', array( 'Sellnow_WP_App_Settings', 'admin_notice' ) );


    }

    /**
     * Load Markdown Class
     */
    public function get_markdown(){

        // Markdown Class
        if (!isset($this->markdown) && !class_exists('Parsedown') )
            require_once( $this->plugin_directory . 'includes/parsedown/Parsedown.php' );

        $this->markdown = new Parsedown();
    }


    /**
     * Reference the plugin by name.
     *
     * @return string plugin name
     */
    public static function social_plugin_name() {
        return __( 'Docs', 'inf_mem' );
    }


    /**
     * Navigate to the settings page through the top-level menu item.
     *
     * @uses add_submenu_page()
     * @param string $parent_slug Facebook top-level menu item slug
     * @return string submenu hook suffix
     */
    public static function add_submenu_item( $parent_slug ) {
        $log = new Inf_Member_Settings_Docs();

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
        global $inf;

        if ( ! class_exists( 'Inf_Member_Settings' ) )
            require_once( dirname(__FILE__) . '/settings.php' );

        echo '<div class="wrap">';
        echo '<header><h2>' . esc_html( self::social_plugin_name() ) . '</h2></header>';

        $this->getDocs();

        if(isset($_GET['doc']) && !empty($_GET['doc']))
            $this->getFile(urldecode($_GET['doc']));
        else
            $this->getFile(urldecode('README.md'));
        echo '</div>';


    }

    /**
     * Get Docs File List
     */
    public function getDocs(){

        $files = array();
        if ($dir = opendir($this->plugin_directory.'docs/')) {
            while (false !== ($file = readdir($dir))) {
                if ($file != "." && $file != ".." && pathinfo($file, PATHINFO_EXTENSION) == 'md') {
                    $files[] = $file;
                }
            }
            closedir($dir);
        }

        if(!empty($files)){
            echo '<ul>';
            foreach($files as $item){
                $url = add_query_arg(array(
                    'page'=> self::PAGE_SLUG,
                    'doc' => $item
                ), admin_url('admin.php'));
              echo "<li><a href='$url'>$item</a></li>";
            }
            echo '</ul>';
        }

    }

    public function getFile($file){

        $dir   = $this->plugin_directory.'docs/';
        $txt   = file_get_contents($dir.$file);

        echo '<h2>Reading '.$file.'</h2><section style="background:#fff; padding:15px;">';
        echo $this->markdown->parse($txt);
        echo '</section>';
    }



}