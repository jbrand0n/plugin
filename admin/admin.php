<?php
/**
 * Admin functions for Plugin
 *
 */
class Inf_Member_Admin {


    /**
     * Load Existing options just once
     * @var array
     */
    public $existing_options = array();


    /**
     * Constructor loads existing options from Settings Members
     * adds the meta boxes to post and pages
     */
    public function __construct() {

        //load options
        $options = get_option('inf_member_members');

        if( !is_array($options) || !isset($options['member']) )
            $this->existing_options = array();
        else
            $this->existing_options = $options['member'];

        //set metabox
        add_action( 'add_meta_boxes', array(&$this,'register_meta_boxes'), 10,2);
        add_action( 'save_post', array(&$this,'save_meta_box') );





        //self::posttype();
    }


    public static function posttype(){
        print_r(get_post_types());
    }


    /**
     * Register the Metaboxes
     *
     * @ref i4w_registerMetaBoxes
     */
    public function register_meta_boxes(){

        $post_types = array( 'post', 'page' );

        foreach( $post_types as $post_type ){
            add_meta_box(
                'group_access',
                __( 'Member Content Protection' ),
                array(&$this,'render_group_access_meta'),
                $post_type,
                'side',
                'high'
            );
        }

    }

    /**
     * Render Metabox fields
     *
     * @param $post
     */
    public function render_group_access_meta($post){

        if( !empty($this->existing_options) ){

            // Add an nonce field so we can check for it later.
            wp_nonce_field( 'render_group_access_meta', 'render_group_access_meta_nonce' );


             //Use get_post_meta() to retrieve an existing value
            $value = get_post_meta( $post->ID, '_inf_member', true );
            //print_r($value);

            //check boxes
            $public = '';
            if( !empty($value['public_only']))
                $public = 'checked';
            $group1 = '';
			
			error_reporting(0);
            if( in_array('-1',$value['group_access']) )
                $group1 = 'checked';


            echo '<ul><li><label><input type="checkbox" value="-1" name="group_access[-1]" id="group_access-1" '.$group1.'>Any logged in user</label></li>
                    <li><label><input type="checkbox" value="1" name="public_only" id="public_only" '.$public.'>Show only to public visitors</label></li>';

            foreach($this->existing_options as $item){
                $chk = '';
                if( in_array($item['tag'],$value['group_access']) )
                    $chk = 'checked';
                echo '<li><label><input type="checkbox" value="'.$item['tag'].'" name="group_access['.$item['tag'].']" id="group_access_'.$item['tag'].'" '.$chk.'>'.$item['name'].'</label></li>';
            }
            echo '</ul>';

        }else{
            echo '<p style="color: red;">Please create membership levels first to show Member options.</p>';
        }

        if( isset($_GET['inf_error']))
            echo '<p style="color:red;">'.urldecode($_GET['inf_error']).'</p>';



    }

    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_box( $post_id ) {

        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

        // Check if our nonce is set.
        if ( ! isset( $_POST['render_group_access_meta_nonce'] ) )
            return $post_id;

        $nonce = $_POST['render_group_access_meta_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'render_group_access_meta' ) )
            return $post_id;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;

        } else {

            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        // OK, its safe for us to save the data now.

        //logic check
        if( !empty($_POST['group_access']) && in_array('-1', $_POST['group_access']) && count($_POST['group_access']) > 1){
            add_filter('redirect_post_location',function($loc) {
                return add_query_arg( 'inf_error', urlencode("Can not be for any logged user and member at the same time."), $loc );
            });
            return $post_id;
        }
        if( !empty($_POST['group_access']) && !empty($_POST['public_only'])){
            add_filter('redirect_post_location',function($loc) {
                return add_query_arg( 'inf_error', urlencode("Can not be public and member at the same time."), $loc );
            });
            return $post_id;
        }

        $post_data = array(
            'group_access'  => $_POST['group_access'],
            'public_only'   => $_POST['public_only']
        );

        // Update the meta field in the database.
        update_post_meta( $post_id, '_inf_member', $post_data );

    }


}