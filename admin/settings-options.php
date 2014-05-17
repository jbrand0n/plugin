<?php

/**
 * Display a options page for application data
 *
 */
class Inf_Member_Settings_Options {

    /**
     * Settings page identifier.
     *
     * @var string
     */
    const PAGE_SLUG = 'inf-member-app-options';

    /**
     * Define our option array value.
     *
     * @var string
     */
    const OPTION_NAME = 'inf_member_options';


    /**
     * The hook suffix assigned by add_submenu_page()
     *
     * @var string
     */
    protected $hook_suffix = '';

    /**
     * Infusion custom form fields
     * @var
     */
    //private $form_fields;
	public $form_fields;

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
		//add_action('wp_ajax_inf-member-action', array($this, 'sync'));
        add_action( 'inf_member_settings_after_header_' . $this->hook_suffix, array( 'Inf_Member_Settings_Options', 'after_header' ) );
		
		$obj = new Inf_Member_Settings_Options;
		
		include_once('pages/settings.php');

        //Inf_Member_Settings::settings_page_template( $this->hook_suffix, __( 'Options', 'inf-member' ) );
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
        return __( 'Options', 'inf_mem' );
    }

    /**
     * Navigate to the settings page through the top-level menu item.
     *
     * @uses add_submenu_page()
     * @param string $parent_slug Facebook top-level menu item slug
     * @return string submenu hook suffix
     */
    public static function add_submenu_item( $parent_slug ) {
        $class = new Inf_Member_Settings_Options();

        $hook_suffix = add_submenu_page(
            $parent_slug,
            self::social_plugin_name(),
            self::social_plugin_name(),
            'manage_options',
            self::PAGE_SLUG,
            array( &$class, 'settings_page' )
        );
 		if ( $hook_suffix ) {
			add_action( 'load-' . $hook_suffix, array( &$class, 'add_script' ) );
		}
       /* if ( $hook_suffix ) {
            $class->hook_suffix = $hook_suffix;
            register_setting( $hook_suffix, self::OPTION_NAME, array( 'Inf_Member_Settings_Options', 'sanitize_options' ) );
            add_action( 'load-' . $hook_suffix, array( &$class, 'onload' ) );
        }*/

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

        //intiate infusion data
        $this->infusion_init();

        // General Options
        $section = 'general';
        /*add_settings_section(
            $section,
            __( 'General Options', 'inf-member' ),
            '',
            $this->hook_suffix
        );
        add_settings_field(
            'admin_bar',
            'Hide WP-Admin Bar',
            array( &$this, 'display_input' ),
            $this->hook_suffix,
            $section,
            array('class' => 'admin_bar', 'id' => 'admin_bar', 'type' => 'checkbox', 'name' => self::OPTION_NAME.'[admin_bar]', 'value' => 'on' )
        );
        add_settings_field(
            'inf_user',
            'Allow Infusion Users',
            array( &$this, 'display_input' ),
            $this->hook_suffix,
            $section,
            array('class' => 'inf_user', 'id' => 'inf_user', 'type' => 'checkbox',
                'name' => self::OPTION_NAME.'[inf_user]', 'value' => 'on',
                'desc' => 'Allow Infusion Users to login as Admin'
            )
        );
        add_settings_field(
            'login_page',
            'Custom Login Page',
            array( &$this, 'display_dropdown' ),
            $this->hook_suffix,
            $section,
            array('class' => 'login_page', 'id' => 'login_page', 'name' => self::OPTION_NAME.'[login_page]', 'default' => 'Default Login Page', 'value' => $this->get_pages_data())
        );*/
        if( !empty($this->form_fields) ){
            /*add_settings_field(
                'pass_field',
                'Password Field',
                array( &$this, 'display_dropdown' ),
                $this->hook_suffix,
                $section,
                array('class' => 'pass_field', 'id' => 'pass_field',
                    'name' => self::OPTION_NAME.'[pass_field]', 'default' => 'Select Password Field', 'value' => $this->form_fields,
                    'desc' => 'If none is selected "Password" is used.')
            );*/
        }else{
            echo '<div id="message" class="error"><p>Please add Infusion API Data to settings.</p> </div>';
        }
    }

    /**
     * Generic input field
     *
     * @param null $args
     */
    public function display_input($args=null){

        $type = 'text';
        if( isset($args['type']) && !empty($args['type']))
            $type = esc_attr($args['type']);

        $html_val = '';
        foreach($args as $k => $arg){
            if($k != 'type' || $k != 'value' || $k != 'desc')
                $html_val .= esc_attr($k).'="'.esc_attr($arg).'" ';
        }

        if( ($existing_value = $this->get_existing_value($args)) != '' ){
            $tmp_chk = '';
            if( isset($args['type']) && ($args['type'] == 'checkbox' || $args['type'] == 'radio') && isset($this->existing_options[$args['id']]) )
                $tmp_chk = 'checked="checked" ';

            $existing_value = 'value="' .  $existing_value . '" '.$tmp_chk;
        }

        echo '<input type="'.$type.'" '.$existing_value.$html_val.' />';
        if(isset($args['desc']))
            echo '<p>'.$args['desc'].'</p>';
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
            if($k != 'selected' || $k != 'value' || $k != 'default' || $k != 'desc' )
                $html_val .= esc_attr($k).'="'.esc_attr($arg).'" ';
        }

        $html = '<select '.$html_val.' >';

        if( isset($args['default']) && !empty($args['default']) )
            $html .= '<option value="0">'.$args['default'].'</option>';

        foreach($args['value'] as $option){

            $selected = '';
            if($existing_value == $option['val'])
                $selected = 'selected="selected"';

            $html .= '<option value="'.$option['val'].'" '.$selected.'>'.$option['name'].'</option>';
        }
        $html .= '</select>';

        if( isset($args['desc']))
            $html .= ' <p>'.$args['desc'].'</p>';

        echo $html;
        //echo 'debug: '.print_r($args,true);
    }

    /**
     * Get Existing Values
     * @param null $args
     * @return string
     */
    private function get_existing_value($args=null){

        $key = $args['id'];
        $existing_value = '';

        //regular type
        if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] ){
            $existing_value = $this->existing_options[$key];
        }

        //default val
        if($existing_value == '' && isset($args['value'])){
            $existing_value = $args['value'];
        }

        return $existing_value;
    }

    /**
     * List all Pages for dropdown
     *
     * @uses get_pages
     * @return array
     */
    public function get_pages_data(){
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
     * Members Initialize data
     * @return mixed
     */
    //private 
	public function infusion_init(){
        global $inf,$iMemDb;

        if(!isset($iMemDb))
            $inf->load_mem_db();

        $form_fields = $iMemDb->run_form_fields();

        if(!empty($form_fields)){
            $field_arr = '';
            foreach($form_fields as $item){
                $field_arr[] = array(
                    'val'  => $item->Name,
                    'name' => $item->Label
                );
            }
            $this->form_fields = $field_arr;
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

        foreach($options as $k => $option){
            if(!empty($option)){
                $clean_options[$k] = sanitize_text_field(trim($option));
            }
        }

        return $clean_options;

    }
	
	public function add_script() {
   		wp_enqueue_script('jquery');
        wp_enqueue_script( 'inf-member-sync', plugins_url( 'static/js/sync.js', dirname( __FILE__ ) ), array('jquery'), '1.0' );
		$variables = array(
								'url' => admin_url( 'admin-ajax.php' ),
								);
			wp_localize_script( 'inf-member-sync', 'inf_member_object',$variables );
        //$this->settings_api_init();
    }
	
	public function sync(){
		global $inf, $iSDK;

        if ( ! class_exists( 'Inf_Member_Settings' ) )
            require_once( dirname(__FILE__) . '/settings.php' );

        $inf->load_php_sdk();
		
		$options = get_option('inf_member_members');
		$inf_tags = array_keys($options[member]);
		
		// Loop: Selected tags for Membership
		foreach($inf_tags as $k=>$tag){
			$returnFields = array('GroupId','ContactGroup','ContactId');
			//$query = array('ContactGroup' => 'Order Sheet EA Sponsored');
			$query = array('GroupId' => $tag);
			$data = $iSDK->Data('query',"ContactGroupAssign",1000,0,$query,$returnFields);		
			
			$count = 0;
			foreach($data as $key=>$val){
				$returnFields = array('Email','Firstname','LastName');
				$query = array('Id' => $val['ContactId']);
				$res = $iSDK->Data('query',"Contact",1,0,$query,$returnFields);
				$email = $username = $res[0]['Email'];
				
				if( username_exists($username)){					
					$user = get_user_by( 'email', $email );
					$user_id = $user->ID;
					
					$groups = get_user_meta($user_id, 'inf-member-groups');
					if(is_array($meta) && !in_array($tag, $groups))
						array_push($groups, $tag);
					else
						$groups = array($tag);						
					
					update_user_meta($user_id, 'inf-member-groups', $groups);					
					wp_update_user(
						array(
							  'ID'          =>    $user_id,
							  'nickname'    =>    $res[0]['Firstname']
							)
						  );						  
				} else {				
					$user_id = wp_create_user( $username, 'p@ssw0rd', $email );
					wp_update_user(
							array(
							  'ID'          =>    $user_id,
							  'nickname'    =>    $res[0]['Firstname']
							)
						  );
					$groups = get_user_meta($user_id, 'inf-member-groups');
					
					if(is_array($groups) && !in_array($tag, $groups))
						array_push($groups, $tag);
					else
						$groups = array($tag);
					
					update_user_meta($user_id, 'inf-member-groups', $groups);					  
				}
				$count++;
				/*if($count >2)
				break;*/
			}
		}
		
		
	}

}