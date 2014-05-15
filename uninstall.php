<?php
/**
 * Remove data written by the plugin for WordPress after an administrative user clicks "Delete" from the plugin management page in the WordPress administrative interface (wp-admin).
 *
 * @todo post meta data
 */

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

// only execute as part of an uninstall script
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

// site options
$__options = array(
	'inf_member'
);

foreach ( $__options as $option_name ) {
	delete_option( $option_name );
}
unset( $__options );

drop_infm_tables();


/**
 * Drop plugin tables
 */
function drop_infm_tables(){
    global $wpdb;
    $pref = $wpdb->prefix;
    $dbname = DB_NAME;
    $q = "show tables from $dbname LIKE '{$pref}infm%'";
    $tbls = $wpdb->get_col($q);
    foreach($tbls as $tbl){
        $q = "DROP TABLE $tbl";
        dbDelta($q);
    }
}