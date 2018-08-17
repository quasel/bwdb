<?php
/*
Plugin Name: Bowling Database v2 (bwdb)
Plugin URI: http://obox-design.com
Description: Allows the user to store and .... Bowling Games.
Author: Tanja Swietli, Bernhard Gronau
Version: 2.0-alpha1
Author URI: http://www.twitter.com/MarcPerel
*/


/* Set constant path to the members plugin directory. */
if ( ! defined( 'BWDB_DIR' ) ) {
	define( 'BWDB_DIR', plugin_dir_path( __FILE__ ) );
}


//When activating the plugin, we must call the install_bwdb_twitter_list() function.
// @todo: DB anlegen/updaten register_activation_hook(__FILE__, "install_bwdb");

//Similarily we can include a function when deactivating the plugin, I chose not to
//@todo: register_deactivation_hook(__FILE__, "delete_bwdb");

/* Launch the plugin. */
add_action( 'plugins_loaded', 'bwdb_setup' );

/**
 * Initialize the plugin.  This function loads the required files needed for the plugin
 * to run in the proper order.
 *
 * @since 0.3.0
 */
function bwdb_setup() {

	$BwDb_shortcodes = new BwDb_shortcodes();
}

//Include the files including the Install, Update and Delete Functions TODO ! automatischer pfad !!!!!;
// @todo: include_once(BWDB_DIR . "bwdb-update.php");

include_once( BWDB_DIR . "bwdb-auswertung.php" );
include_once( BWDB_DIR . "bwdb-shortcodes.php" );



// Debug
function print_bwdb( $ar, $name = 'Variable' ) {
	echo "<pre>";
	echo $name . ":<br />";
	print_r( $ar );
	echo "</pre>";

}

// New Stuff for BWDB 2.0

function bwdb_klss_ssn_title( $pieces, $is_new_item ) {

	//check if is new item, if not return $pieces without making any changes
	if ( ! $is_new_item ) {
		// return $pieces;
	}
	//make sure that all three fields are active
	$fields = array( 'post_title', 'rel_klss', 'rel_ssn' );
	foreach ( $fields as $field ) {
		if ( ! isset( $pieces['fields_active'][ $field ] ) ) {
			array_push( $pieces['fields_active'], $field );
		}
	}
	//set variables for fields empty first for saftey's sake
	$rel_klss = $rel_ssn = '';
	//get value of "rel_klss" if possible
	if ( isset( $pieces['fields']['rel_klss'] ) && isset( $pieces['fields']['rel_klss']['value'] ) ) {
		$rel_klss_id = current( $pieces['fields']['rel_klss']['value'] );
		$rel_klss    = get_the_title( $rel_klss_id );

	}
	//get value of "rel_ssn" if possible
	if ( isset( $pieces['fields']['rel_ssn'] ) && isset( $pieces['fields']['rel_ssn']['value'] ) ) {
		$rel_ssn_id = current( $pieces['fields']['rel_ssn']['value'] );
		$rel_ssn    = get_the_title( $rel_ssn_id );

	}

	//set post title using $rel_klss and $rel_ssn
	$pieces['object_fields']['post_title']['value'] = $rel_ssn . ', ' . $rel_klss;

	// wp_die( '<pre>' . print_bwdb( $pieces) . '</pre>' );


	//return $pieces to save
	return $pieces;
}

add_filter( 'pods_api_pre_save_pod_item_klss_ssn', 'bwdb_klss_ssn_title', 10, 2 );

function bwdb_sktn_klss_ssn_title( $pieces, $is_new_item ) {

	//check if is new item, if not return $pieces without making any changes
	if ( ! $is_new_item ) {
		// return $pieces;
	}
	//make sure that all three fields are active
	$fields = array( 'post_title', 'rel_klss_ssn', 'rel_sktn' );
	foreach ( $fields as $field ) {
		if ( ! isset( $pieces['fields_active'][ $field ] ) ) {
			array_push( $pieces['fields_active'], $field );
		}
	}
	//set variables for fields empty first for saftey's sake
	$rel_klss_ssn = $rel_sktn = '';
	//get value of "rel_klss" if possible
	if ( isset( $pieces['fields']['rel_klss_ssn'] ) && isset( $pieces['fields']['rel_klss_ssn']['value'] ) ) {
		$rel_klss_ssn_id = current( $pieces['fields']['rel_klss_ssn']['value'] );
		$rel_klss_ssn    = get_the_title( $rel_klss_ssn_id );

	}
	//get value of "rel_ssn" if possible
	if ( isset( $pieces['fields']['rel_sktn'] ) && isset( $pieces['fields']['rel_sktn']['value'] ) ) {
		$rel_sktn_id = current( $pieces['fields']['rel_sktn']['value'] );
		$rel_sktn    = get_the_title( $rel_sktn_id );

	}

	//set post title using $rel_klss and $rel_ssn
	$pieces['object_fields']['post_title']['value'] = $rel_klss_ssn . ', ' . $rel_sktn;

	// wp_die( '<pre>' . $pieces['object_fields']['post_title']['value'] . '</pre>' );


	//return $pieces to save
	return $pieces;
}

add_filter( 'pods_api_pre_save_pod_item_sktn_klss_ssn', 'bwdb_sktn_klss_ssn_title', 10, 2 );

function bwdb_set_post_title_splr( $pieces, $is_new_item ) {
	//make sure that all three fields are active
	$fields = array( 'post_title' );
	foreach ( $fields as $field ) {
		if ( ! isset( $pieces['fields_active'][ $field ] ) ) {
			array_push( $pieces['fields_active'], $field );
		}
	}

	//set variables for fields empty first for saftey's sake
	$vorname = $nachname = $pnr = '';
	//get value of "rel_klss" if possible
	if ( isset( $pieces['fields']['vorname'] ) && isset( $pieces['fields']['vorname']['value'] ) ) {
		$vorname = $pieces['fields']['vorname']['value'];

	}
	//get value of "rel_ssn" if possible
	if ( isset( $pieces['fields']['nachname'] ) && isset( $pieces['fields']['nachname']['value'] ) ) {
		$nachname = $pieces['fields']['nachname']['value'];

	}
	//get value of "rel_ssn" if possible
	if ( isset( $pieces['fields']['pnr'] ) && isset( $pieces['fields']['pnr']['value'] ) ) {
		$pnr = $pieces['fields']['pnr']['value'];

	}

	//set post title using $rel_klss and $rel_ssn
	$pieces['object_fields']['post_title']['value'] = $vorname . ' ' . $nachname . ' (' . $pnr . ')';

	// wp_die( '<pre>' . $pieces['object_fields']['post_title']['value'] . '</pre>' );


	//return $pieces to save
	return $pieces;
}

add_filter( 'pods_api_pre_save_pod_item_splr', 'bwdb_set_post_title_splr', 10, 2 );


// Gravity Forms
// an “isSelected” property (which is used to indicate whether the option is currently selected or not)
// https://docs.gravityforms.com/dynamically-populating-drop-down-fields/


add_filter( 'gform_pre_render', 'populate_sktn' );
add_filter( 'gform_pre_validation', 'populate_sktn' );
add_filter( 'gform_pre_submission_filter', 'populate_sktn' );
add_filter( 'gform_admin_pre_render', 'populate_sktn' );
function populate_sktn( $form ) {

	// print_bwdb( array(), 'test' );
	// wp_die( print_bwdb( $form ) );

	foreach ( $form['fields'] as &$field ) {

		if ( $field->type != 'select' || strpos( $field->cssClass, 'populate-sktn' ) === false ) {
			continue;
		}

		// you can add additional parameters here to alter the posts that are retrieved
		// more info: http://codex.wordpress.org/Template_Tags/get_posts
		$posts = get_posts( 'numberposts=-1&post_status=publish&post_type=sktn_klss_ssn' );

		$choices = array();

		foreach ( $posts as $post ) {
			$choices[] = array( 'text' => $post->post_title, 'value' => $post->ID, 'isSelected' => '0' );
		}

		// update 'Select a Post' to whatever you'd like the instructive option to be
		$field->placeholder = 'Select a Sektion';
		$field->choices     = $choices;

	}

	return $form;

}// Gravity Forms
add_filter( 'gform_pre_render_1', 'populate_splr' );
add_filter( 'gform_pre_validation_1', 'populate_splr' );
add_filter( 'gform_pre_submission_filter_1', 'populate_splr' );
add_filter( 'gform_admin_pre_render_1', 'populate_splr' );
function populate_splr( $form ) {

	// print_bwdb( $form);
	foreach ( $form['fields'] as &$field ) {

		if ( $field->type != 'select' || strpos( $field->cssClass, 'populate-splr' ) === false ) {
			continue;
		}


		$_POST['sktn_id'];

		// $name = 'sktn_id'
		// you can add additional parameters here to alter the posts that are retrieved
		// more info: http://codex.wordpress.org/Template_Tags/get_posts
		$posts = get_posts( array(
				'numberposts' => - 1,
				'post_status' => 'publish',
				'post_type'   => 'splr',
				'meta_key'    => 'rel_sktn_klss_ssn',
				'meta_value'  => $_GET['sktn_id'], // @todo: ABSICHERN !!!! optimieren ...
			)
		);

		$choices = array();

		foreach ( $posts as $post ) {
			$choices[] = array( 'text' => $post->post_title, 'value' => $post->ID );
		}

		// update 'Select a Post' to whatever you'd like the instructive option to be
		$field->placeholder = 'Select a Spieler';
		$field->choices     = $choices;

	}

	return $form;
}

add_action( 'gform_after_submission_1', 'access_entry_via_field', 10, 2 );

function access_entry_via_field( $entry, $form ) {
	// Get the book pod object
	$pod = pods( 'spl' );

// To add a new item, let's set the data first
	$fields = array(
		'rel_splr'          => rgar( $entry, '12' ),
		'rel_sktn_klss_ssn' => rgar( $entry, '13' ),
		'datum'             => rgar( $entry, '5' ),
		'runde'             => rgar( $entry, '1' ),
		'nummer'            => '1',
		'ergebnis'          => rgar( $entry, '2' ),
		'reserve'           => rgar( $entry, '17' ),
	);

	if ( ! empty( $fields['ergebnis'] ) ) {
		$new[] = $pod->add( $fields );
	}
	// Spiel 2
	$fields['nummer']   = '2';
	$fields['ergebnis'] = rgar( $entry, '3' );

	if ( ! empty( $fields['ergebnis'] ) ) {
		$new[] = $pod->add( $fields );
	}

	// Spiel 3
	$fields['nummer']   = '3';
	$fields['ergebnis'] = rgar( $entry, '4' );

	if ( ! empty( $fields['ergebnis'] ) ) {
		$new[] = $pod->add( $fields );
	}


	//$data = print_bwdb( $entry ) . print_bwdb( $form );
	// wp_die( print_bwdb( $fields, 'angelegt') );

}

// add_filter( 'gform_field_value', 'my_custom_population_function', 10, 3 );
function my_custom_population_function( $value, $field, $name ) {
	// wp_die( print_bwdb( $field ));
	print_bwdb( $name, 'name' );
	// print_bwdb( $field, 'Field_Test' );
	print_bwdb( $value, 'Value' );

	return $value;
}

// add_filter( 'gform_form_args', 'setup_form_args', 10, 1 );

function setup_form_args( $args ) {
	// print_bwdb( $args, 'Form' );
	// wp_die( print_bwdb( $args));
	$form_args = array(
		'display_title'       => false,
		'display_description' => true,
	);

	return $args;
}