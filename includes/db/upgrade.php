<?php
error_reporting(0);
/**
 * Install Tables for current version
 *
 */

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class Inf_Member_Upgrade {

    /**
     * Current Version of DB
     * @var string
     */
    private $cur_ver;

    /**
     * DB PLugin path
     * @var string
     */
    private $plugin_directory;

    /**
     * Initialize with an options array.
     *
     * @param array $options existing options
     */
    public function __construct( $options = array() ) {
        global $inf;

        $this->plugin_directory = dirname(__FILE__) . '/sql/';

        $v = $inf->credentials;
        if(!isset($v['db_ver'])){
            $this->cur_ver = '0.0.0';
            $v->db_ver = $this->cur_ver;
        }else{
            $this->cur_ver = $v['db_ver'];
        }

    }

    /**
     * Run initial options
     */
    public function init(){

        if($this->check_update()){
            $url = add_query_arg(array(
                'page'=> $_GET['page'],
                'update_db' => true
            ), admin_url('admin.php'));
            echo '<div id="message" class="error"><p>Important DB updates are available. Click the button to update.<br><br><button type="button" class="button button-primary" onclick="window.location=\''.$url.' \'">Update</button></p> </div>';
        }else{
            echo '<div id="message" class="updated"><p>No DB updates available.</p></div>';
        }

    }

    /**
     * Updates the DB according to version number.
     *
     */
    public function run_updates(){
        global $inf,$wpdb;
        $pref = $wpdb->prefix;
        $opt  = Inf_Member::OPTION_NAME;

        $files = $this->get_sql_files();
        if(empty($files)){
            echo '<div id="message" class="error"><p>No DB updates to run.</p></div>';
        }else{
            //run each update file
            foreach($files as $file){
                if(version_compare($this->cur_ver,$file, '<' )){

                    $query = str_replace('{pref}',$pref, file_get_contents($this->plugin_directory.$file) );
                    $r = dbDelta($query);
                    //var_dump($r);
                    //update sys vars
                    $this->cur_ver = str_replace('.sql','',$file);
                    $inf->credentials['db_ver'] = $this->cur_ver;
                    update_option( $opt, $inf->credentials );
                }

            }
            echo '<div id="message" class="updated"><p>Updated DB to version '.$this->cur_ver.'</p></div>';
        }

    }

    /**
     * Check if DB updates are available
     *
     * @return bool true if needs update
     */
    public function check_update(){

        $files = $this->get_sql_files();
        $version = str_replace('.sql','',end($files));

        //compare versions
        if(empty($files)){
            return false;
        }elseif(version_compare($this->cur_ver,$version, '>=' ) ){
            return false;
        }elseif(version_compare($this->cur_ver,$version, '<' ) ){
            return true;
        }
    }

    /**
     * Get all SQL Files for the update directory
     * @return array
     */
    public function get_sql_files(){
        $files = array();
        if ($dir = opendir($this->plugin_directory)) {
            while (false !== ($file = readdir($dir))) {
                if ($file != "." && $file != ".." && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                    $files[] = $file;
                }
            }
            closedir($dir);
        }

        return $files;
    }
}