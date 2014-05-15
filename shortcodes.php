<?php
/**
 * Convert shortcodes into HTML data-* elements for interpretation by the Facebook JavaScript SDK
 *
 */
class Inf_Member_Shortcodes {

	/**
	 * Register shortcode handlers
	 *
	 * @uses add_shortcode()
	 * @uses wp_embed_register_handler()
	 * @return void
	 */
	public static function init() {

		// expose social plugin markup using WordPress Shortcode API
		add_shortcode( 'facebook_like_button', array( 'Inf_Member_Shortcodes', 'like_button' ) );
		


	}


	/**
	 * Generate a HTML login form
	 *
	 * @param array $attributes shortcode attributes. overrides site options for specific button attributes
	 * @param string $content shortcode content. no effect
	 * @return string login form div HTML or empty string if minimum requirements not met
	 */
	public static function login_form( $attributes, $content = null ) {
		global $post;

		$site_options = get_option( 'facebook_like_button' );
		if ( ! is_array( $site_options ) )
			$site_options = array();

		$options = shortcode_atts( array(
			'href' => '',
			'share' => isset( $site_options['share'] ) && $site_options['share'],
			'layout' => isset( $site_options['layout'] ) ? $site_options['layout'] : '',
			'show_faces' => isset( $site_options['show_faces'] ) && $site_options['show_faces'],
			'width' => isset( $site_options['width'] ) ? $site_options['width'] : 0,
			'action' => isset( $site_options['action'] ) ? $site_options['action'] : '',
			'font' => isset( $site_options['font'] ) ? $site_options['font'] : '',
			'colorscheme' => isset( $site_options['colorscheme'] ) ? $site_options['colorscheme'] : '',
			'ref' => 'shortcode'
		), $attributes, 'facebook_like_button' );

		// check for valid href value. unset if not valid, allowing for a possible permalink replacement
		if ( is_string( $options['href'] ) && $options['href'] )
			$options['href'] = esc_url_raw( $options['href'], array( 'http', 'https' ) );
		if ( ! ( is_string( $options['href'] ) && $options['href'] ) ) {
			unset( $options['href'] );
			if ( isset( $post ) )
				$options['href'] = apply_filters( 'facebook_rel_canonical', get_permalink( $post->ID ) );
		}

		foreach ( array( 'share', 'show_faces' ) as $bool_key ) {
			$options[$bool_key] = (bool) $options[$bool_key];
		}
		$options['width'] = absint( $options['width'] );
		if ( $options['width'] < 1 )
			unset( $options['width'] );

		foreach( array( 'layout', 'action', 'font', 'colorscheme', 'ref' ) as $key ) {
			$options[$key] = trim( $options[$key] );
			if ( ! $options[$key] )
				unset( $options[$key] );
		}

		if ( ! function_exists( 'facebook_get_like_button' ) )
			require_once( dirname(__FILE__) . '/social-plugins.php' );

		return facebook_get_like_button( $options );
	}
	
}

