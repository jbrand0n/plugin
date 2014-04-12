<?php
/**
 * Plugin Name: Members Plugin
 * Plugin URI: http://maskcode.com
 * Description: 
 * Version: 0.2.4
 * Author: Mohit Suthar
 * Author URI: http://maskcode.com

 */

/**
 * @since 0.2.0
 */
class Members_Load {

	/**
	 * PHP5 constructor method.
	 *
	 * @since 0.2.0
	 */
	function __construct() {
		global $members;

		/* Set up an empty class for the global $members object. */
		$members = new stdClass;

		/* Set the constants needed by the plugin. */
		add_action( 'plugins_loaded', array( &$this, 'constants' ), 1 );

		/* Internationalize the text strings used. */
		add_action( 'plugins_loaded', array( &$this, 'i18n' ), 2 );

		/* Load the functions files. */
		add_action( 'plugins_loaded', array( &$this, 'includes' ), 3 );

		/* Load the admin files. */
		add_action( 'plugins_loaded', array( &$this, 'admin' ), 4 );

		/* Register activation hook. */
		register_activation_hook( __FILE__, array( &$this, 'activation' ) );
	}

	/**
	 * Defines constants used by the plugin.
	 *
	 * @since 0.2.0
	 */
	function constants() {

		/* Set the version number of the plugin. */
		define( 'MEMBERS_VERSION', '0.2.4' );

		/* Set the database version number of the plugin. */
		define( 'MEMBERS_DB_VERSION', 2 );

		/* Set constant path to the members plugin directory. */
		define( 'MEMBERS_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

		/* Set constant path to the members plugin URL. */
		define( 'MEMBERS_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

		/* Set the constant path to the members includes directory. */
		define( 'MEMBERS_INCLUDES', MEMBERS_DIR . trailingslashit( 'includes' ) );

		/* Set the constant path to the members admin directory. */
		define( 'MEMBERS_ADMIN', MEMBERS_DIR . trailingslashit( 'admin' ) );
	}

	/**
	 * Loads the initial files needed by the plugin.
	 *
	 * @since 0.2.0
	 */
	function includes() {

		/* Load the plugin functions file. */
		require_once( MEMBERS_INCLUDES . 'functions.php' );

		/* Load the update functionality. */
		require_once( MEMBERS_INCLUDES . 'update.php' );

		/* Load the deprecated functions file. */
		require_once( MEMBERS_INCLUDES . 'deprecated.php' );

		/* Load the admin bar functions. */
		require_once( MEMBERS_INCLUDES . 'admin-bar.php' );

		/* Load the functions related to capabilities. */
		require_once( MEMBERS_INCLUDES . 'capabilities.php' );

		/* Load the content permissions functions. */
		require_once( MEMBERS_INCLUDES . 'content-permissions.php' );

		/* Load the private site functions. */
		require_once( MEMBERS_INCLUDES . 'private-site.php' );

		/* Load the shortcodes functions file. */
		require_once( MEMBERS_INCLUDES . 'shortcodes.php' );

		/* Load the template functions. */
		require_once( MEMBERS_INCLUDES . 'template.php' );

		/* Load the widgets functions file. */
		require_once( MEMBERS_INCLUDES . 'widgets.php' );
	}

	/**
	 * Loads the translation files.
	 *
	 * @since 0.2.0
	 */
	function i18n() {

		/* Load the translation of the plugin. */
		load_plugin_textdomain( 'members', false, 'members/languages' );
	}

	/**
	 * Loads the admin functions and files.
	 *
	 * @since 0.2.0
	 */
	function admin() {

		/* Only load files if in the WordPress admin. */
		if ( is_admin() ) {

			/* Load the main admin file. */
			require_once( MEMBERS_ADMIN . 'admin.php' );

			/* Load the plugin settings. */
			require_once( MEMBERS_ADMIN . 'settings.php' );
		}
	}

	/**
	 * Method that runs only when the plugin is activated.
	 *
	 * @since 0.2.0
	 */
	function activation() {

		/* Get the administrator role. */
		$role = get_role( 'administrator' );

		/* If the administrator role exists, add required capabilities for the plugin. */
		if ( !empty( $role ) ) {

			/* Role management capabilities. */
			$role->add_cap( 'list_roles' );
			$role->add_cap( 'create_roles' );
			$role->add_cap( 'delete_roles' );
			$role->add_cap( 'edit_roles' );

			/* Content permissions capabilities. */
			$role->add_cap( 'restrict_content' );
		}

		/**
		 * If the administrator role does not exist for some reason, we have a bit of a problem 
		 * because this is a role management plugin and requires that someone actually be able to 
		 * manage roles.  So, we're going to create a custom role here.  The site administrator can 
		 * assign this custom role to any user they wish to work around this problem.  We're only 
		 * doing this for single-site installs of WordPress.  The 'super admin' has permission to do
		 * pretty much anything on a multisite install.
		 */
		elseif ( empty( $role ) && !is_multisite() ) {

			/* Add the 'members_role_manager' role with limited capabilities. */
			add_role(
				'members_role_manager',
				_x( 'Role Manager', 'role', 'members' ),
				array(
					'read' => true,
					'list_roles' => true,
					'edit_roles' => true
				)
			);
		}
	}
}

$members_load = new Members_Load();

?>