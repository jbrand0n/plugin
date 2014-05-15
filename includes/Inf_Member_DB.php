<?php

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class Inf_Member_DB {

    /**
     * DB Wrapper
     * @var
     */
    private $db;

    /**
     * DB Prefix
     * @var
     */
    private $pref;

    /**
     * Infusionsoft DBS Fields
     * @var
     */
    public $dbsFIELDS;

    public function __construct(){
        global $inf,$iSDK,$wpdb;
        $this->db = $wpdb;
        $this->pref = $wpdb->prefix;

        if( !isset($iSDK) )
            $inf->load_php_sdk();

        $this->inf_api_fields();
    }

    public function tests(){
        $returnFields = array('Id','GroupName','GroupCategoryId');
        $query = array('GroupCategoryId' => '138');
        //$data = $iSDK->Data('query',"ContactGroup",1000,0,$query,$returnFields);

        //get groups
        $returnFields = array('Id','CategoryName');
        $query = array('Id' => '%');
        //$data = $iSDK->Data('query',"ContactGroupCategory",1000,0,$query,$returnFields);
        echo '<pre>';
        //print_r($data);
        echo '</pre>';
    }

    /**
     * Create Tag Categories Table and return all
     *
     * @var bool refresh to true to reload
     * @return array
     */
    public function run_tag_cat($refresh=false){

        if(!$this->check_tag_cat() || $refresh)
            $this->populate_tag_cat();

        return $this->get_tag_cat();
    }

    /**
     * Checks if Tag Categories have been loaded
     *
     * @return bool true if loaded
     */
    public function check_tag_cat(){
        $result = $this->db->get_col("SELECT count('Id') from {$this->pref}infm_tag_cat");
        if($result[0] >= 1)
            return true;
        else
            return false;
    }

    /**
     * Get all Tag Categories
     * @return array
     */
    public function get_tag_cat(){
        $data = $this->db->get_results("SELECT * FROM {$this->pref}infm_tag_cat ORDER BY CategoryName ASC");
        return $data;
    }

    /**
     * Populate Category Tags Table
     *
     */
    private function populate_tag_cat(){
        global $iSDK;

        //get groups
        $returnFields = array('Id','CategoryName');
        $query = array('Id' => '%');
        $data = $iSDK->Data('query',"ContactGroupCategory",1000,0,$query,$returnFields);

        // stop if no cats
        if(empty($data))
            return;
        //empty table to avoid dups
        $this->db->get_results("TRUNCATE TABLE {$this->pref}infm_tag_cat");

        foreach($data as $item){
            $this->db->insert($this->pref.'infm_tag_cat',$item);
        }


    }

    /**
     * Create Tag Table and return all
     *
     * @var bool refresh to true to reload
     * @return array
     */
    public function run_tags($refresh=false){

        $options = get_option( Inf_Member_Settings_Members::OPTION_NAME );

        if( !isset($options['tag_cat']) && !empty($options['tag_cat']) ){
            echo '<div id="message" class="error"><p>You must provide a Category Group ID</p> </div>';
            return;
        }


        if(!$this->check_tags() || $refresh)
            $this->populate_tags($options['tag_cat']);

        return $this->get_tags();

    }

    /**
     * Check if Tags have been loaded
     *
     * @return bool true if loaded
     */
    public function check_tags(){
		$options = get_option( Inf_Member_Settings_Members::OPTION_NAME );
		
        $result = $this->db->get_col("SELECT count('Id') from {$this->pref}infm_contact_group");
		
		$row = $this->db->get_col("SELECT GroupCategoryId from {$this->pref}infm_contact_group limit 0,1");
		
        if($result[0] >= 1 && $row[0] == $options['tag_cat'] )
            return true;
        else
            return false;
    }

    /**
     * Get all Tags
     *
     * @return array
     */
    public function get_tags(){
        $data = $this->db->get_results("SELECT * FROM {$this->pref}infm_contact_group ORDER BY GroupName ASC");
        return $data;
    }

    /**
     * Populate Tags Table
     *
     * @param $group_id
     * @return mixed
     */
    private function populate_tags($group_id){
        global $iSDK;

        $returnFields = array('Id','GroupName','GroupCategoryId');
        $query = array('GroupCategoryId' => $group_id);

        $data = $iSDK->Data('query',"ContactGroup",1000,0,$query,$returnFields);

        // stop if no cats
        if(empty($data))
            return;
        //empty table to avoid dups
        $this->db->get_results("TRUNCATE TABLE {$this->pref}infm_contact_group");

        foreach($data as $item){
            $this->db->insert($this->pref.'infm_contact_group',$item);
        }
    }


    /**
     * Create Form Fields and return all
     *
     * @var bool refresh to true to reload
     * @return array
     */
    public function run_form_fields($refresh=false){

        if(!$this->check_form_fields() || $refresh)
            $this->populate_form_fields();

        return $this->get_form_fields();

    }

    /**
     * Check if Form Fields have been loaded
     *
     * @return bool true if loaded
     */
    public function check_form_fields(){
        $result = $this->db->get_col("SELECT count('Id') from {$this->pref}inf_DataFormField");
        if($result[0] >= 1)
            return true;
        else
            return false;
    }

    /**
     * Get all Form Fields
     *
     * @return array
     */
    public function get_form_fields(){
        $data = $this->db->get_results("SELECT * FROM {$this->pref}inf_DataFormField WHERE DataType IN(10, 15, 16, 18, 19) ORDER BY Label ASC");
        return $data;
    }

    /**
     * Populate Form Fields Table
     *
     * @ref http://help.infusionsoft.com/developers/tables/dataformfield
     * @return void
     */
    private function populate_form_fields(){
        global $iSDK;

        $data = $iSDK->Data( 'query', 'DataFormField', 1000, 0, array('FormId' => 0 - 1), array('Id', 'Label', 'Name', 'GroupId', 'FormId') );

        // stop if no data
        if(empty($data))
            return;

        //empty table to avoid dups
        $this->db->get_results("TRUNCATE TABLE {$this->pref}inf_DataFormField");

        foreach($data as $item){
            $this->db->insert($this->pref.'inf_DataFormField',$item);
        }
    }



    /**
     * Get Infusion user or client via email and password for WP-Login
     *
     * @ref http://help.infusionsoft.com/api-docs/dataservice#authenticateUser
     * @ref http://help.infusionsoft.com/developers/tables/dataformfield
     *
     * @param $email string
     * @param $pw string
     * @param $check_user bool default false
     * @return array
     */
    public function get_user($email,$pw, $check_user=false){
        global $iSDK;

        $return_data = array( 'is_admin' => false, 'error' => false, 'msg' => '' );

        // get password field
        $pw_field = 'Password';
        $opt = get_option( 'inf_member_options' );
        if( isset($opt['pass_field']) && !empty($opt['pass_field']))
            $pw_field = '_'.$opt['pass_field'];

        // get return fields and add custom pw field
        $returnFields = $this->dbsFIELDS;
        $returnFields[] = $pw_field;

        $query = array('Email' => $email);
        $user = $iSDK->Data('query','Contact',10,0,$query,$returnFields);

        if( !empty($user) )
            $return_data['user'] = $user[0];

        $tag_cat = get_option( 'inf_member_members' );
        if( isset($tag_cat['tag_cat']) )
            $tag_cat = $tag_cat['tag_cat'];
        else
            return array_merge($return_data, array( 'error' => true, 'msg' => 'Please setup first Tag Categories', 'user' => $user ) );

        // Contact groups
        if( !empty($user) && in_array($tag_cat, explode(',',$user[0]['Groups'])) ){
            $data = $iSDK->Data( 'query', 'ContactGroupAssign', 1000, 0, array( 'ContactId' => $user[0]['Id'], 'GroupId' => $tag_cat ), array( 'ContactGroup' ) );

            //flatten array
            if( empty($data) ){
                $return_data['tags'] = array();
            }else{
                $tags = array();
                foreach($data as $item){
                    $tags[] = $item['ContactGroup'];
                }
                $return_data['tags'] = $tags;
            }
        }


        // if authenticate user
        if($check_user){
            //hash md5 password if not a md5 hash for authenticate user only
            $pw_md5 = $pw;
            if( !(strlen($pw) == 32 && ctype_xdigit($pw)) )
                $pw_md5 = md5($pw);


            $inf_user = $iSDK->Data('authenticateUser',$email,$pw_md5);
            if(is_wp_error($inf_user))
                return array_merge($return_data, array( 'error' => true, 'msg' => $inf_user->get_error_message(), 'user' => $user ) );
            else
                $return_data['is_admin'] = true;
        }

        return $return_data;
    }

    /**
     * Get Infusion Password field for user
     * @todo get password field from options
     * @param int $id
     * @return array
     */
    private function get_infusion_password($id = 0) {
        global $iSDK;
        $opt = get_option( 'inf_member_options' );

        if ( ( $id != 0 && $pw = $iSDK->Data( 'Contact', 1, 0, array( 'Id' => $id ), array( 'Id', 'FirstName', 'LastName', 'Email', 'Groups', $opt['pass_field']/*$i4w->PASSFIELD*/ ) ) )) {
            return $pw[0];
        }

        return array();
    }


    /**
     * Set Infusion dbs field names and save it in transient
     *
     * @ref https://codex.wordpress.org/Transients_API
     * @uses transient
     * @return null
     */
    public function inf_api_fields(){


        if( ($tbl_api = get_transient('inf_api_fields') !== false) && is_array( $tbl_api ) && isset( $tbl_api['dbsFIELDS']) ){
            return null;
        }

        // Load Fieldnames from Infusionsoft
        $csv = '';
        $i = 1;
        $set_transient = false;

        while ($i <= 3) {
            if ( ($csv = file_get_contents( 'https://infusionsoftapitablefields.s3.amazonaws.com/fieldnames.csv') ) && substr($csv, 0, 9) == 'dbsFIELDS' ) {
                $set_transient = 14 * HOUR_IN_SECONDS;
                break;
            }

            sleep(2);
            ++$i;
        }

        if( !$set_transient){
            $csv = file_get_contents( dirname(plugin_dir_path( __FILE__ )).'/static/files/fieldnames.csv', true);
            $set_transient = 2 * HOUR_IN_SECONDS;
        }

        $csv_arr = explode( "\n", trim( $csv ) );

        foreach ($csv_arr as $k => $line) {
            $arrLIST = explode( ',', trim( $line ) );

            $varTABLE = array_shift( $arrLIST );
            $arrTABLES[$varTABLE] = $arrLIST;
        }


        $this->dbsFIELDS = $arrTABLES['dbsFIELDS'];
        unset($arrTABLES);

        set_transient( 'inf_api_fields', $this->dbsFIELDS, $set_transient );

    }





}