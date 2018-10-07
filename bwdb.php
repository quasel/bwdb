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
		$rel_klss_id = $pieces['fields']['rel_klss']['value'];
		$rel_klss    = get_the_title( $rel_klss_id );

	}
	//get value of "rel_ssn" if possible
	if ( isset( $pieces['fields']['rel_ssn'] ) && isset( $pieces['fields']['rel_ssn']['value'] ) ) {
		$rel_ssn_id = $pieces['fields']['rel_ssn']['value'];
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
		$rel_klss_ssn_id = $pieces['fields']['rel_klss_ssn']['value'];
		$rel_klss_ssn    = get_the_title( $rel_klss_ssn_id );

	}
	//get value of "rel_ssn" if possible
	if ( isset( $pieces['fields']['rel_sktn'] ) && isset( $pieces['fields']['rel_sktn']['value'] ) ) {
		$rel_sktn_id = $pieces['fields']['rel_sktn']['value'];
		$rel_sktn    = get_the_title( $rel_sktn_id );

	}

	//set post title using $rel_klss and $rel_ssn
	$pieces['object_fields']['post_title']['value'] = $rel_klss_ssn . ', ' . $rel_sktn;

	// wp_die( '<pre>' .  current( $pieces['fields']['rel_klss_ssn']['value'] ) . ' | ' . print_bwdb( $pieces['fields']['rel_klss_ssn']['value'] ). ' | ' . $rel_sktn_id . ' | ' . $pieces['fields']['rel_sktn']['value']  . ' | ' . '</pre>' );


	//return $pieces to save
	return $pieces;
}

add_filter( 'pods_api_pre_save_pod_item_sktn_klss_ssn', 'bwdb_sktn_klss_ssn_title', 10, 2 );

// add_filter( 'pods_api_save_pod_item_track_changed_fields_splr', '__return_true');
// add_filter( 'pods_api_save_pod_item_track_changed_fields_splr', 'bwdb_test', 10, 2 );
function bwdb_test($track, $params) {
	wp_die( var_dump( $track ), print_bwdb( $params ) );
	return true;
}

function bwdb_set_post_title_splr( $pieces, $is_new_item ) {
	//make sure that all three fields are active
	$fields = array( 'post_title' );
	foreach ( $fields as $field ) {
		if ( ! isset( $pieces['fields_active'][ $field ] ) ) {
			array_push( $pieces['fields_active'], $field );
		}
	}

	//This line allows it to target only the Pod called 'cpt'.
	//For all Pods use:  $PodsAPI = pods_api();
	// $changes = PodsAPI::handle_changed_fields( $pieces['params']->pod, $pieces['params']->id, 'get' );
	// wp_die( '<pre>' . print_bwdb( $pieces ) . $pieces['params']->pod . $pieces['params']->id . '</pre>' );
	// $pieces['changed_fields'] with add_filter( 'pods_api_save_pod_item_track_changed_fields_splr', '__return_true');

	//set variables for fields empty first for saftey's sake
	$nachname = $vorname = $pnr = '';

	if ( ! $is_new_item ) {
		$pod     = pods( $pieces['params']->pod, $pieces['params']->id );
		$vorname  = $pod->field( 'vorname' );
		$nachname = $pod->field( 'nachname' );
		$pnr      = $pod->field( 'pnr' );
	}

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

	$post_title = $vorname . ' ' . $nachname . ' (' . $pnr . ')';
	$pieces['object_fields']['post_title']['value'] = $post_title;

	// wp_die( '<pre>' . print_bwdb( $pieces['fields']) . '</pre>' );
	//return $pieces to save
	return $pieces;
}

add_filter( 'pods_api_pre_save_pod_item_splr', 'bwdb_set_post_title_splr', 10, 2 );


// alternativ for List View - https://github.com/pods-framework/pods/issues/5119
// @todo: überprüfen namen optimieren & ins forum posten mit referenz auf original post ... https://github.com/pods-framework/pods/issues/1240
// zwei Versionen - 2te ev doch besser ?
/**
 * @param $data
 * @param $name
 * @param $value
 * @param $options
 * @param $pod
 * @param $id
 *
 * @return mixed
 */
function bwdb_pods_field_pick_data( $data, $name, $value, $options, $pod, $id ) {

	// wp_die( '<pre>' . print_bwdb( $data ) . '</pre>' );

	// print_bwdb($name);
	// print_bwdb($value);
	if ( in_array( $name, array( 'pods_meta_rel_sktn', 'pods_meta_dropdown', 'pods_meta_radio', 'pods_meta_autocomplete', 'pods_meta_list_view' ) ) ) {

		$pod = pods( 'sktn', array(
			'limit'      => - 1,
			'expires'    => "DAY_IN_SECONDS",
			'cache_mode' => "transient"
		) );

		if ( 0 < $pod->total() ) {
			while ( $pod->fetch() ) {
				$rel_bwrb = $pod->display( 'rel_bwrb' );
				if ( $data[ $pod->id() ] ) {
					$data[ $pod->id() ] .= ' - ' . $rel_bwrb;
				}
			}
		}
	}

	// print_bwdb($data);
	return $data;
}

add_filter('pods_field_pick_data', 'bwdb_pods_field_pick_data', 1, 6);
// add_filter('pods_field_pick_data_ajax_items', 'bwdb_pods_field_pick_data', 1, 6);
// add_filter('pods_field_dfv_data', 'bwdb_pods_field_pick_data', 1, 6);


// Gravity Forms
// an “isSelected” property (which is used to indicate whether the option is currently selected or not)
// https://docs.gravityforms.com/dynamically-populating-drop-down-fields/
// https://docs.gravityforms.com/gform_chained_selects_input_choices/  for extend to allow ssn / klss_ssn / ...

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


		//@todo make dynamic ( get latests post of ssn -> ID or add a settings page for it! ) and maybe add chained selects -> klss
		$ssn_id = '189555';
		$choices = array();

		$pod = pods( 'sktn_klss_ssn', array(
			'expires'    => "DAY_IN_SECONDS",
			'cache_mode' => "transient",
			'where'      => "rel_klss_ssn.rel_ssn.ID = $ssn_id",
			'limit'      => "-1",
		) );

		if ( 0 < $pod->total() ) {
			while ( $pod->fetch() ) {
				$choices[] = array( 'text' => $pod->field('post_title'), 'value' => $pod->field('ID'), 'isSelected' => '0' );
			}
		}

		// update 'Select a Post' to whatever you'd like the instructive option to be
		$field->placeholder = 'Select a Sektion';
		$field->choices     = $choices;

	}

	return $form;

}


// Gravity Forms
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

		$sktn_id = pods_v_sanitized( 'sktn_id', 'get', '');

		// you can add additional parameters here to alter the posts that are retrieved
		// more info: http://codex.wordpress.org/Template_Tags/get_posts
		$posts = get_posts( array(
				'numberposts' => - 1,
				'post_status' => 'publish',
				'post_type'   => 'splr',
				'meta_key'    => 'rel_sktn_klss_ssn',
				'meta_value'  => $sktn_id,
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
		'nummer'            => '',
		'ergebnis'          => '',
		'reserve'           => rgar( $entry, '17' ),
	);

	// Spiel 1
	$fields['nummer']   = '1';
	$fields['ergebnis'] = rgar( $entry, '2' );
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

	// Spiel 4
	$fields['nummer']   = '4';
	$fields['ergebnis'] = rgar( $entry, '31' );

	if ( ! empty( $fields['ergebnis'] ) ) {
		$new[] = $pod->add( $fields );
	}
	// Spiel 5
	$fields['nummer']   = '5';
	$fields['ergebnis'] = rgar( $entry, '30' );

	if ( ! empty( $fields['ergebnis'] ) ) {
		$new[] = $pod->add( $fields );
	}

	// Spiel 6
	$fields['nummer']   = '6';
	$fields['ergebnis'] = rgar( $entry, '29' );

	if ( ! empty( $fields['ergebnis'] ) ) {
		$new[] = $pod->add( $fields );
	}



	//$data = print_bwdb( $entry ) . print_bwdb( $form );
	// wp_die( print_bwdb( $fields, 'angelegt') );

}

// Preparation for "Spielzettel Eingabe"
function bwdb_gf_pods_save( array $entry, array $entry_id, array $pod, array $fields ) {
// Spiel 1
	for ( $i = 1; $i <= 6; $i++ ) {
		$fields['nummer']   = $i;
		$fields['ergebnis'] = rgar( $entry, $entry_id[$i] );
		if ( ! empty( $fields['ergebnis'] ) ) {
			$pod->add( $fields );
		}
	}
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