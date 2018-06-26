<?php

/**
 * @author Alex Rabe, Vincent Prat
 *
 * @since 1.0.0
 * @description Use WordPress Shortcode API for more features
 * @Docs http://codex.wordpress.org/Shortcode_API
 */
class BwDb_Shortcodes {

	// register the new shortcodes
	function __construct() {

		//Long posts should require a higher limit, see http://core.trac.wordpress.org/ticket/8553
		//@ini_set('pcre.backtrack_limit', 500000);


		// do_shortcode on the_excerpt could causes several unwanted output. Uncomment it on your own risk
		// add_filter('the_excerpt', array(&$this, 'convert_shortcode'));
		// add_filter('the_excerpt', 'do_shortcode', 11);

		add_shortcode( 'bwdb', array( &$this, 'bwdbShow' ) );

	}

	/**
	 * Function to ...
	 *
	 * default ist schnitt - auch notwendig damit der Array Merge ohne Angabe von Parametern funktioniert ...
	 *
	 *
	 * @param array $attr
	 *
	 * @return nothing
	 */
	function bwdbShow( $attr = array( 'show' => 'schnitt' ), $content = '' ) {


		//zusammenführen von Attributen
		$merged = array_merge( $attr, $_GET );

		//Standard Werte Setzten - nicht definiertes fliegt raus ...
		$final = shortcode_atts( array(
			'ssn_id'           => '',
			'klss_id'          => '',
			'klss_ssn_id'      => '',
			'sktn_id'          => '',
			'sktn_klss_ssn_id' => '',
			'vrn_id'           => '',
			'splr_id'          => '',
			'sex'              => '',
			'reserve'          => '',
			'min'              => '1',
			'show'             => '',
			'runde'            => '',
			'output'           => '',
			'limit'            => '',
			'orderby'          => '',
			'id'               => 'bwdb',
			'title'            => '',
		), $merged );

		$debug = false;
		if ( true == $debug && current_user_can( 'manage_options' ) ) {
			print_bwdb( home_url(), 'Home');

			$base = home_url();

			$link = add_query_arg( array(
				'test' => 'test',
				'test2' => 'test2'
			), $base );

			print_bwdb( $link );
		}

		bwdbShowAvg( $final );

	}
}

?>