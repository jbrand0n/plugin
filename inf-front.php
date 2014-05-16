<?php
/**
 * Frontend functions for Infusion Member
 *
 */
class Inf_Member_Front {


    /**
     * If Membership levels even exist
     * @var bool
     */
    private $_hierarchy = false;

    /**
     * Member Levels array, sorted to hierachy if it applies
     * @var array
     */
    private $_member_levels = array();

    /**
     * Infusion Options from admin/settings_options.php
     *
     * @uses Inf_Member_Settings_Options
     * @var array
     */
    public $inf_opt = array();


    public function __construct(){
        //print_r('<h1>tests</h1>');

        // load Infusion members options
        $inf_opt = get_option( 'inf_member_options' );
        if ( ! is_array( $inf_opt ) )
            $inf_opt = array();
        $this->inf_opt = $inf_opt;
        unset( $inf_opt );

        add_filter( 'the_posts', array(&$this, 'filter_pages_posts'), 7, 2 );
        add_filter( 'get_pages', array(&$this, 'filter_pages_posts'), 7, 2 );
        add_filter( 'login_url', array(&$this, 'infmem_login_url'), 10, 2 );

        //add_filter( 'login_redirect', 'i4w_login_redirect_filter', 999, 3 );
        //add_action( 'login_init', 'i4w_login_init' );


        //$this->test('test@test.com','t3cht3am');
    }

    public function init(){

    }

    public function test($email,$pw){
        global $iMemDb, $wp_session, $user_ID;

        // user is logged in default false
        $logged_in = false;

        //get user and check password
        $wp_user     = get_user_by('email',$email);
        $wp_pw_check = false;
        if( isset($wp_user->data->ID) ){
            if( wp_check_password($pw,$wp_user->data->user_pass,$wp_user->data->ID) )
                $wp_pw_check = true;
        }

        // @todo function last_login is not working
        // login user if password checks out and last_login is under a day
        if($wp_pw_check && isset($wp_user->data->last_login) && $this->last_login_valid($wp_user->data->last_login) ){
            $logged_in = $this->login_user($wp_user->data->ID);
            if(!$logged_in)
                echo '<div id="message" class="error"><p>Login failed.</p></div>';
        }else{

            //get infusion user data
            $inf_user = $iMemDb->get_user($email,$pw);

            $create_user = false;
            $user_type   = 'subscriber';

            if( !isset($inf_user['user']) || empty($inf_user['user']) ){
                // do error user not found
            }else{

                // if admin user enabled and is infusion backend user
                if( isset($this->inf_opt['inf_user']) && $inf_user['is_admin'] )
                    $user_type = 'editor';

                //check tags if not admin

                if( isset($inf_user['tags']) && !empty($inf_user['tags']) ){
                    $this->member_levels();
                    $this->has_membership_level($inf_user['tags']);
                }

                //check password -- if updated in infusion

                // create user


            }

            /*
            do_action( 'wp_login_failed', $wp_user->data->user_login );
            WP_Error;
            return new ( 'invalid_username', i4w_message( 20 ) );
            */
        }

        //$inf_user = $iMemDb->get_user($email,$pw);
        print_r($inf_user);





    }

    /**
     * @todo not done yet
     */
    public function set_options(){

        //show adminbar option
        if( !is_admin() && $this->inf_opt['admin_bar'] == 1 ) {
            add_filter( 'admin_bar', '__return_false' );
        }



    }

    /**
     * Member Levels sorted by levels
     */
    public function member_levels(){

        //get member levels
        $this->_member_levels = get_option('inf_member_members');
        $this->_member_levels = $this->_member_levels['member'];

        //if more as one member level check for levels
        if(count($this->_member_levels) > 1){
            foreach($this->_member_levels as $level){
                if($level['level'] != 0){
                    $this->_hierarchy = true;
                    break;
                }
            }
        }

        // if hierarchy sort array by levels desc
        if($this->_hierarchy){

            $tmp_levels = array();
            foreach ($this->_member_levels as $key => $row)
            {
                $tmp_levels[$key] = $row['level'];
            }
            array_multisort($tmp_levels, SORT_DESC,SORT_NUMERIC, $this->_member_levels);

        }
    }

    public function has_membership_level($user_tags){

        if( empty($user_tags))
            return false;
        // @todo get tag ids from infusion 
        //$this->_member_levels
    }


    //@todo complete permissions
    public function filter_pages_posts($posts){
		
		
		$options = get_option('inf_member_members');
		
		//print_r($current_user);
        //print_r($posts);
		
		//$value = get_post_meta( $posts[0]->ID, '_inf_member', true );
		//print_r($value['group_access']);
        //if( empty( $posts ) || $this->is_admin() )
            //return $posts;

        //run through each post or page
        foreach($posts as $k => $post){
			$value = get_post_meta( $post->ID, '_inf_member', true );
			if($value){
				if($value['public_only']){
					if(is_user_logged_in() && !is_super_admin()){
						echo "only for public";
						exit;
					}
				} else if($value['group_access']){
					if(!is_user_logged_in()){
						echo "only for logged in users";
						exit;
					}
					
					if($value['group_access'][-1] != -1){
						global $current_user;
						
						//echo $current_user->ID;
						$user_tags = get_user_meta($current_user->ID, 'inf-member-groups');
						//print_r($user_tags);
						$groups = $value['group_access'];
						//print_r($groups);
						$show_posts = 0;
						
						foreach($user_tags[0] as $key=>$tag){
							//print_r($tag);exit;
							if(in_array($tag, $groups))
								$show_posts = 1;	
						}
						
						if(!$show_posts){
							echo "DSFSDFDSF";
							exit;	
						}
						
						//print_r($current_user);
						
					}
				}
			} 
			//print_r($value['group_access']);
			
            //check if post_status == publish then permissions

        }
    }




    /**
     * Check if current user has admin permissions
     * returns true if admin permissions
     *
     * @return bool
     */
    public static function is_admin() {
        if( !is_multisite() ){
            return current_user_can('activate_plugins');
        }

        return ( is_super_admin() || current_user_can_for_blog($GLOBALS['blog_id'], 'administrator') );
    }


    /**
     * Login URL redirect to custom login page
     *
     * @todo needs more testing
     * @return bool|string|void
     */
    public function infmem_login_url($id = 0){

        if( isset($this->inf_opt['login_page']) && $this->inf_opt['login_page'] == 0) {
            return site_url( 'wp-login.php' );
        }

        return get_permalink( $this->inf_opt['login_page'] );
    }

    /**
     * Login User Programmatically
     *
     * @ref http://wpcoke.com/log-in-a-wordpress-user-programmatically/
     * @ref http://iandunn.name/programmatically-sign-on-a-wordpress-user/
     * @param int $user_id
     * @return bool
     */
    private static function login_user($user_id){

        if ( is_user_logged_in() ) {
            wp_logout();
        }

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        if ( is_user_logged_in() ){
            return true;
        }

        return false;
    }

    /**
     * Check if last login was within 24h
     *
     * @param time() $last_login
     * @return bool
     */
    private static function last_login_valid($last_login=null){
        if(is_null($last_login))
            return false;
        if( (time() - $last_login / 86400 ) < 1 )
            return true;

        return false;
    }

    /**
     * Create User in WP
     *
     * @ref http://tommcfarlin.com/create-a-user-in-wordpress/
     * @param array $user_data
     */
    private static function create_user($user_data){

    }

}