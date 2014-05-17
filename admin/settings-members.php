<?php
/**
 * Display a settings page for application data
 *
 */
class Inf_Member_Settings_Members {

    /**
     * Settings page identifier.
     *
     * @var string
     */
    const PAGE_SLUG = 'inf-mem-app-members';

    /**
     * Define our option array value.
     *
     * @var string
     */
    const OPTION_NAME = 'inf_member_members';

    /**
     * The hook suffix assigned by add_submenu_page()
     *
     * @var string
     */
    protected $hook_suffix = '';

    /**
     * Tag Categories array
     * @var array
     */
    protected $tag_cat;

    /**
     * Tags for Memberships
     * @var
     */
    protected $tags;

    /**
     * Initialize with an options array.
     *
     * @param array $options existing options
     */
    public function __construct( $options = array() ) {
        global $inf;

        if ( is_array( $options ) && ! empty( $options ) )
            $this->existing_options = $options;
        else
            $this->existing_options = array();

        //load Infusion SDK
        $inf->load_php_sdk();
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

        wp_enqueue_style( 'admin_member', plugins_url( 'static/css/admin_member.css', dirname( __FILE__ ) ), array(), '1.0' );
        wp_enqueue_script( 'admin_member', plugins_url( 'static/js/admin_member.js', dirname( __FILE__ ) ), array(), '1.0' );

        $this->settings_api_init();
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

        self::settings_page_template( $this->hook_suffix, __( 'Membership Setup', 'inf-member' ) );
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
     * Optional Content after header.
     *
     * @return void
     */
    public static function after_header() {

    }

    /**
     * Reference the plugin by name.
     *
     * @return string plugin name
     */
    public static function social_plugin_name() {
        return __( 'Members', 'inf_mem' );
    }


    /**
     * Navigate to the settings page through the top-level menu item.
     *
     * @uses add_submenu_page()
     * @param string $parent_slug Facebook top-level menu item slug
     * @return string submenu hook suffix
     */
    public static function add_submenu_item( $parent_slug ) {
        $class = new Inf_Member_Settings_Members();

        $hook_suffix = add_submenu_page(
            $parent_slug,
            self::social_plugin_name(),
            self::social_plugin_name(),
            'manage_options',
            self::PAGE_SLUG,
            array( &$class, 'settings_page' )
        );

        if ( $hook_suffix ) {
            $class->hook_suffix = $hook_suffix;
            //register_setting( $hook_suffix, self::OPTION_NAME, array( 'Inf_Member_Settings', 'sanitize_options' ) );
            register_setting( $hook_suffix, self::OPTION_NAME, array( 'Inf_Member_Settings_Members', 'sanitize_options' ) );
            add_action( 'load-' . $hook_suffix, array( &$class, 'onload' ) );
        }

        return $hook_suffix;
    }

    /**
     * Hook into the settings API.
     *
     * @uses add_settings_section()
     * @uses add_settings_field()
     * @return void
     */
    private function settings_api_init() {
        global $inf;

        if ( ! isset( $this->hook_suffix ) )
            return;

        /*if(version_compare($inf->credentials['db_ver'],'0.0.0.0', '<')){
            echo '<div id="message" class="error"><p>Please install DB first.</p> </div>';
            return;
        }*/

        // Initialize Member Data
        $this->members_init();

        // Tag Categories
        if(is_array($this->tag_cat)){
            $section = 'category';
            add_settings_section(
                $section,
                __( 'Tag Category', 'inf-member' ),
                '',
                $this->hook_suffix
            );
            add_settings_field(
                'tag_cat',
                'Select Tag Category',
                array( &$this, 'display_tag_cat' ),
                $this->hook_suffix,
                $section,
                ''
            );
        }
        // Membership Levels
        if(isset($this->existing_options['tag_cat']) && !is_null($this->existing_options['tag_cat'])){

            $section = 'memberships';
            add_settings_section(
                $section,
                'Membership Levels',
                '',
                $this->hook_suffix
            );

            $opt = self::OPTION_NAME;

            // new rows
            $rows = array( $opt.'[new][0]', $opt.'[new][1]', $opt.'[new][2]', $opt.'[new][3]');
            //print_r($this->existing_options['member']);

            // load existing options into the rows array
            if(isset($this->existing_options['member'])){
                $exist_rows = array();
                foreach($this->existing_options['member'] as $k => $item){
                    array_push($exist_rows, $opt.'[mem]['.$k.']');
                }
                $rows = array_merge($exist_rows,$rows);
            }

            foreach($rows as $item){

                // Mem Tag
                add_settings_field(
                    'mem_tag_'.$item,
                    'Tag Name',
                    array( &$this, 'display_dropdown' ),
                    $this->hook_suffix,
                    $section,
                    array('class' => 'mem_tag', 'default' => 'Select Membership', 'value' => $this->tags, 'name' => $item.'[tag]' )
                );

                // mem level
                add_settings_field(
                    'mem_level_'.$item,
                    'Level',
                    array( &$this, 'display_input' ),
                    $this->hook_suffix,
                    $section,
                    array('value' => 0, 'class' => 'mem_level', 'maxlength' => 2, 'size' => 2, 'pattern' => '[0-9]+', 'name' => $item.'[level]')
                );

                //Membership Name
                add_settings_field(
                    'mem_name_'.$item,
                    'Membership Name',
                    array( &$this, 'display_input' ),
                    $this->hook_suffix,
                    $section,
                    array('placeholder' => 'Membership Name', 'class' => 'mem_name', 'maxlength' => 32, 'size' => 22, 'pattern' => '[a-zA-Z0-9-_]+', 'name' => $item.'[name]' )
                );

                // Redirect on login
                add_settings_field(
                    'login_page_'.$item,
                    'Redirect on login',
                    array( &$this, 'display_dropdown' ),
                    $this->hook_suffix,
                    $section,
                    array('class' => 'login_page', 'default' => 'Default Site Url', 'value' => $this->get_pages_data(), 'name' => $item.'[login]' )
                );
                //Redirect on logout
                add_settings_field(
                    'logout_page_'.$item,
                    'Redirect on logout',
                    array( &$this, 'display_dropdown' ),
                    $this->hook_suffix,
                    $section,
                    array('class' => 'logout_page', 'default' => 'Default Site Url', 'value' => $this->get_pages_data(), 'name' => $item.'[logout]' )
                );
            }

            //$this->content();

        }else{
            echo '<div id="message" class="error"><p>Please select a Tag Category first.</p> </div>';
            return;
        }
        //print_r($this->existing_options);

    }

    /**
     * List all Pages for dropdown
     *
     * @uses get_pages
     * @return array
     */
    private function get_pages_data(){
        $data = array();
        $args = array(
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 0,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => 0,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        );
        $pages = get_pages($args);

        if(empty($pages))
            return;

        foreach($pages as $page){
            $data[] = array(
                'val'  => $page->ID,
                'name' => $page->post_title
            );
        }

        return $data;
    }


    /**
     * Display Tag Categories Dropdown
     *
     * @return void
     */
    public function display_tag_cat() {
        $key = 'tag_cat';
        $id = $key;

        if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
            $existing_value = $this->existing_options[$key];
        else
            $existing_value = '';

        settings_errors( $id );

        $html = '<select name="'. self::OPTION_NAME . '[' . $key . ']">';
        $html .= '<option value="0">Select a Membership</option>';
        foreach((array)$this->tag_cat as $item){
            $sel = '';
            if($item->Id == $existing_value)
                $sel = 'selected="selected"';
            $html .= '<option value="'.$item->Id.'" '.$sel.'>'.$item->CategoryName.'</option>';
        }
        $html .= '</select><p>Select the Infusionsoft Tag Category in which your membership tags are stored.</p>';
        echo $html;
    }

    /**
     * Generic input field
     *
     * @param null $args
     */
    public function display_input($args=null){
        $key = $args['key'];
        $id = $key;

        $type = 'text';
        if( isset($args['type']) && !empty($args['type']))
            $type = esc_attr($args['type']);

        $html_val = '';
        foreach($args as $k => $arg){
            if($k != 'type' || $k != 'value')
                $html_val .= esc_attr($k).'="'.esc_attr($arg).'" ';
        }

        $existing_value = $this->get_existing_value($args);

        echo '<input type="'.$type.'" value="' . esc_attr( $existing_value ) . '" '.$html_val.' />';
        //echo 'debug: '.print_r($args,true);
    }

    /**
     * Generic Dropdown
     *
     * @param null $args
     */
    public function display_dropdown($args=null){

        $key = $args['key'];
        $id = $key;
        $existing_value = $this->get_existing_value($args);

        $html_val = '';
        foreach($args as $k => $arg){
            if($k != 'selected' || $k != 'value' || $k != 'default' )
                $html_val .= esc_attr($k).'="'.esc_attr($arg).'" ';
        }

        $html = '<select '.$html_val.' >';

        if( isset($args['default']) && !empty($args['default']) )
            $html .= '<option value="">'.$args['default'].'</option>';

        foreach($args['value'] as $option){

            $selected = '';
            if($existing_value == $option['val'])
                $selected = 'selected="selected"';

            $html .= '<option value="'.$option['val'].'" '.$selected.'>'.$option['name'].'</option>';
        }
        $html .= '</select>';
        echo $html;
        //echo 'debug: '.print_r($args,true);
    }

    /**
     * Get Existing Values
     * @param null $args
     * @return string
     */
    private function get_existing_value($args=null){

        $key = $args['key'];
        $existing_value = '';

        //regular type
        if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] ){
            $existing_value = $this->existing_options[$key];

        //members
        }elseif(isset($this->existing_options['member'])){

            // members key
            $k1 = explode('][',str_replace(self::OPTION_NAME,'',$args['name']));
            if($k1[0] == '[mem'){
                $val = $k1[1];
                $key = str_replace(']','',$k1[2]);
                $key_sel = array_keys($this->existing_options['member']);

                $existing_value = $this->existing_options['member'][$val][$key];
            }
        }
        //default val
        if($existing_value == '' && isset($args['value'])){
            $existing_value = $args['value'];
        }
        return $existing_value;
    }

    /**
     * Members Initialize data
     * @return mixed
     */
    private function members_init(){
        global $inf,$iMemDb;

        if(!isset($iMemDb))
            $inf->load_mem_db();

        $this->tag_cat = $iMemDb->run_tag_cat();
		
        $tags = $iMemDb->run_tags();
        if(!empty($tags)){
            $tags_arr = '';
            foreach($tags as $tag){
                $tags_arr[] = array(
                    'val'  => $tag->Id,
                    'name' => $tag->GroupName
                );
            }
            $this->tags = $tags_arr;
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
        self::custom_section( $page_slug );

        submit_button();
        echo '</form>';
        echo '</div>';

        /**
         * Echo content at the bottom of the page.
         */
        do_action( 'inf_mem_settings_footer_' . $page_slug );
    }

    /**
     * Prints out all settings sections added to a particular settings page
     *
     * Originally part of settings api
     *
     * @global $wp_settings_sections Storage array of all settings sections added to admin pages
     * @global $wp_settings_fields Storage array of settings fields and info about their pages/sections
     * @param string $page The slug name of the page whos settings sections you want to output
     */
    public static function custom_section( $page ) {
        global $wp_settings_sections, $wp_settings_fields;

        if ( ! isset( $wp_settings_sections[$page] ) )
            return;

        echo '<div class="wrap">';
        foreach ( (array) $wp_settings_sections[$page] as $section ) {
            if ( $section['title'] )
                echo "<h3>{$section['title']}</h3>\n";

            if ( $section['callback'] )
                call_user_func( $section['callback'], $section );

            if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
                continue;

            //member header row
            if($section['id'] == 'memberships'){
                echo '<div class="row '.$section['id'].' headers">';
                $i=0;
                foreach($wp_settings_fields[$page][$section['id']] as $item){
                    $id = explode('_',$item['id']);
                    if($i <= 4)
                        echo '<div class="field '.$id[0].'_'.$id[1].'">'.$item['title'].'</div>';
                    $i++;
                }
                echo '</div>';
            }

            echo '<div class="row '.$section['id'].' content">';
            self::custom_fields( $page, $section['id'] );
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Print out the settings fields for a particular settings section
     *
     * Part of the Settings API. Use this in a settings page to output
     * a specific section. Should normally be called by do_settings_sections()
     * rather than directly.
     *
     * @global $wp_settings_fields Storage array of settings fields and their pages/sections
     *
     * @param string $page Slug title of the admin page who's settings fields you want to show.
     * @param section $section Slug title of the settings section who's fields you want to show.
     */
    public static function custom_fields($page, $section) {
        global $wp_settings_fields;

        if ( ! isset( $wp_settings_fields[$page][$section] ) )
            return;

        foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
            $css_class = '';

            if ( !empty($field['args']['class']) )
                $css_class = $field['args']['class'];

            echo '<div class="field '.$css_class.'">';

            call_user_func($field['callback'], $field['args']);

            echo '</div>';

        }
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

            if( !empty($option) && $k == 'tag_cat'){
                $clean_options[$k] = sanitize_text_field(trim($option));

            }elseif(!empty($option) && ($k == 'new' || $k == 'mem' )){

                foreach($option as $item){
                    if( !empty($item['tag']) && !empty($item['name']) ){
                       $tmp = $item;
                       //unset($tmp['tag']);
                        $clean_options['member'][$item['tag']] = $tmp;

                       //trim fields
                        $clean_options['member'][$item['tag']]['name']  = sanitize_text_field(trim($clean_options['member'][$item['tag']]['name']));
                        $clean_options['member'][$item['tag']]['level'] = sanitize_text_field(trim($clean_options['member'][$item['tag']]['level']));

                    }elseif(!empty($item['tag']) && !empty($item['name'])){
                        add_settings_error( $k, $k.'-error', __( 'Fields can not be empty', 'inf-member' ) );
                    }
                }
            }

        }

        if(!$error)
            return $clean_options;
        else
            return array();
    }

}