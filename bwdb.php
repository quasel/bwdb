<?php
/*
Plugin Name: bwdb Bowling Database
Plugin URI: http://obox-design.com
Description: Allows the user to store and .... Bowling Games.
Author: Tanja Swietli, Bernhard Gronau
Version: 0
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
	global $wpdb;

	// @todo: alternative finden
	$wpdb->bewerb        = $wpdb->prefix . 'bwdb_bewerb';
	$wpdb->saison        = $wpdb->prefix . 'bwdb_saison';
	$wpdb->klasse        = $wpdb->prefix . 'bwdb_klasse';
	$wpdb->klss_ssn      = $wpdb->prefix . 'bwdb_klss_ssn';
	$wpdb->sektion       = $wpdb->prefix . 'bwdb_sektion';
	$wpdb->sktn_klss_ssn = $wpdb->prefix . 'bwdb_sktn_klss_ssn';
	$wpdb->spiel         = $wpdb->prefix . 'bwdb_spiel';
	$wpdb->spieler       = $wpdb->prefix . 'bwdb_spieler';
	// $wpdb->splr_sktn = $wpdb->prefix . 'bwdb_splr_sktn';
	$wpdb->splr_sktn_klss_ssn = $wpdb->prefix . 'bwdb_splr_sktn_klss_ssn';
	$wpdb->verein             = $wpdb->prefix . 'bwdb_verein';
	// $wpdb->bewerb= $wpdb->prefix . 'bwdb_bewerb'; - nicht mehr im DB_Schema ...


	$plugin_prefix = "bwdb_";


//	/* Load the translation of the plugin. */
//	load_plugin_textdomain( 'query-posts', false, '/query-posts/languages' );
//
//	/* Load the plugin's widgets. */
//	add_action( 'widgets_init', 'query_posts_load_widgets' );
//
//	/* Create shortcodes. */
//	add_action( 'init', 'query_posts_shortcodes', 11 );

// add_shortcode( 'list_spieler', 'bwdb_list_spieler' );
// add_shortcode( 'list_spiele', 'bwdb_list_spiele' );
	$BwDb_shortcodes = new BwDb_shortcodes();
}

//Include the files including the Install, Update and Delete Functions TODO ! automatischer pfad !!!!!;
// @todo: include_once(BWDB_DIR . "bwdb-update.php");

include_once( BWDB_DIR . "bwdb-auswertung.php" );
include_once( BWDB_DIR . "bwdb-class-list-table-spiele.php" );
include_once( BWDB_DIR . "bwdb-class-list-table-spieler.php" );
include_once( BWDB_DIR . "bwdb-class-list-table-vereine.php" );
include_once( BWDB_DIR . "bwdb-class-list-table-sektionen.php" );
include_once( BWDB_DIR . "bwdb-class-list-table-klassen.php" );
include_once( BWDB_DIR . "bwdb-shortcodes.php" );


function install_bwdb() {
	require_once( BWDB_DIR . "bwdb-setup.php" );
	bwdb_setup_db();
}

function delete_bwdb() {
	require_once( BWDB_DIR . "bwdb-delete.php" );
	delete_bwdb();
}


//If we're posting from a form to this page, perform the relevant functions BEFORE any other headers are passed
if ( isset( $_POST["bwdb_update"] ) ) {
	add_action( "init", "bwdb_update" );
}
if ( isset( $_POST["bwdb_delete"] ) ) {
	add_action( "init", "bwdb_delete" );
}


//Add the Menu Item, which will load the twitter_list_menu() function
// @todo: Rechte System optimieren
add_action( "admin_menu", "bwdb_menu" );

function bwdb_menu() {
	add_menu_page( "Bowling", "Bowling", 'edit_posts', 'bwdb', 'bwdb_insert' );
	// add_submenu_page('bwdb', 'Auswertung', 'Auswertung', 8, 'bwdb'.'_auswertung', bwdb_auswertung);
	add_submenu_page( 'bwdb', 'Klasse', 'Klasse', 'activate_plugins', 'bwdb' . '_klassen', 'bwdb_list_klassen' );
	add_submenu_page( 'bwdb', 'Verein', 'Verein', 'activate_plugins', 'bwdb' . '_vereine', 'bwdb_list_vereine' );
	add_submenu_page( 'bwdb', 'Sektion', 'Sektion', 'activate_plugins', 'bwdb' . '_sektionen', 'bwdb_list_sektionen' );
	add_submenu_page( 'bwdb', 'Spieler', 'Spieler', 'edit_posts', 'bwdb' . '_spieler', 'bwdb_list_spieler' );
	add_submenu_page( 'bwdb', 'Spiele', 'Spiele', 'activate_plugins', 'bwdb' . '_spiele', 'bwdb_list_spiele' );
}

//Create the landing page/form
function bwdb_insert() {

	//NB Always set wpdb globally!
	global $wpdb;

	// $do = (isset($_GET["do"]);
	//	$action = bwdb_current_action();
	//	echo "$action";


	//This would output '/client/?s=word&foo=bar&baz=tiny'
	//$arr_params = array ( 'foo' => 'bar', 'baz' => 'tiny' );
	//echo add_query_arg( $arr_params );
	?>
	<h1>Eingabe Bowling DB</h1>
	<!-- Show page messages -->


	<?php
	$action = $_REQUEST['action'];


	//überprüfen der Werte ... && isset($_REQUEST['action']
	//@todo fertigstellen funktioniert noch niczht ganz und überall ;)

	if ( $_REQUEST['runde'] < 1 ) {
		$errors[] = 'Runde > 1 erforderlich';
	}

	if ( $_REQUEST['klss_ssn_id'] < 1 ) {
		$errors[] = 'Bitte eine Klasse auswählen';
	}

	if ( isset( $errors ) && isset( $_REQUEST['check'] ) ) {
		bwdb_message( $errors );
	}


	switch ( $action ) {
		case "edit":
			bwdb_form_edit();
			// no break after edit - prepares the data !!!!
			bwdb_form_show();
			break;
		case "show":
			bwdb_form_show();
			break;
		case "check":
			bwdb_form_show();
			break;
		case "save":
			bwdb_form_save();
			break;
		default:
			bwdb_form_choose();
			break;
	}

	// $debug = true;
	//Kontrollausgabe
	if ( 'true' == $debug ) {
		echo "<pre>";
		print_r( $_REQUEST );
		echo "</pre>";
		echo $wpdb->last_query();
		$wpdb->show_errors();
		$wpdb->print_error();
	}
}

function bwdb_message( $errors ) {
	echo '<div id="error" class="error">';
	foreach ( $errors as $error ) {
		echo "<li>$error</li>";
	}
	echo '</div>';
}

// Ermitteln von Runde / Manschafft etc
function bwdb_form_choose() {

	$attr['ssn_id'] = '6';   // @todo Saison über DB ermitteln!!!

	?>
	<div class="form-wrap">

		<form method="POST" action="<?php $_SERVER[ REQUEST_URI ] ?>">
			<input type="hidden" name="action" value="show"/>

			<div class="form-field">
				<label for="klss_ssn_id">Bitte Bewerb/Klasse auswählen</label>
				<?php bwdb_dropdown( 'klss_ssn', 'klss_ssn_id', $attr ); ?>
			</div>
			<div class="form-field form-required">
				<label for="runde">Runde: </label>
				<input type="text" name="runde" value="0" size="40">

				<p><?php _e( 'Bitte die einzutragende Runde eingeben' ); ?></p>
			</div>
			<div class="form-field form-required">
				<label for="runde">Datum: </label>
				<input type="text" name="date" value="<?php echo date( 'Y-m-d' ); ?>">

				<p><?php _e( 'Bitte im Format YYYY-MM-DD eingeben - zum Beispiel 2012-09-01' ); ?></p>
			</div>
			<div class="form-field">
				<label for="runde">Reserve: </label>
				<input type="checkbox" name="reserve" value="1">

				<p><?php _e( 'Achtung! Bitte auswählen für Reservespiele' ); ?></p>
			</div>
			<?php submit_button( "Bestätigen", 'primary', 'button' ); ?>
		</form>
	</div>

	<?php
}

function bwdb_form_show() {
	global $wpdb;

	$action      = $_REQUEST['action'];
	$runde       = $_REQUEST['runde'];
	$date        = $_REQUEST['date'];
	$klss_ssn_id = $_REQUEST['klss_ssn_id'];
	$reserve     = $_REQUEST['reserve'];
	$klss_ssn    = $wpdb->get_row( "SELECT name, anz_spiele, max_spieler FROM $wpdb->klss_ssn as k LEFT JOIN $wpdb->klasse AS kn ON kn.klss_id = k.klss_id WHERE klss_ssn_id = '$klss_ssn_id'" );

	$messages[1] = __( 'Bitte Werte kontrolieren - Manschaftsergebnis stimmt nicht überein' );

	echo '<h2> Klasse: ' . $klss_ssn->name . ' - Runde: ' . $runde . ' - Datum: ' . $date;
	if ( 1 == $reserve ) {
		echo ' Reserve';
	}
	echo '</h2>';
	echo '<form method="POST" action="' . $_SERVER[ REQUEST_URI ] . '">';


	// Schleifen für die Eingabe MaxSpieler / Spiele einer Runde
	// Überschriften
	/* vorbereitung check !!!!
	 <div class="form-field form-required">
		<label for="ergebnis_check">Manschaftsergebnis</label>
		<input type="text" name="ergebnis_check" value="<?php echo $_REQUEST[ergebnis_check]; ?>" />
	</div>  */
	?>
	<table class="form-table wp-list-table">
	<tr class="form-field form-required">
		<th scope="col" valign="top">Spieler Sektion ID/Name</th>
		<th scope="col" valign="top">Spieler ID</th>
		<?php
		for ( $i = 1; $i <= $klss_ssn->anz_spiele; ++ $i ) {
			echo '<th scope="col" valign="top">' . $i . '.Spiel</th>';
		}
		?>
		<th scope="col" valign="top">Summe</th>
	</tr>

	<?php
	// Anzahl der Zeilen = max_spieler
	$summe_gesamt = 0;

	for ( $j = 1; $j <= $klss_ssn->max_spieler; ++ $j ) {
		$post_splr_id          = 'spl[' . $j . '][splr_id])';
		$post_sktn_klss_ssn_id = 'spl[' . $j . '][sktn_klss_ssn_id])';
		$splr_id               = $_REQUEST['spl'][ $j ]['splr_id'];

		if ( ! empty( $splr_id ) && is_numeric( $splr_id ) ) {
			$spieler          = $wpdb->get_row( "SELECT vorname, nachname FROM $wpdb->spieler WHERE splr_ID = $splr_id" );
			$sktn_klss_ssn_id = $wpdb->get_var( "SELECT s.sktn_klss_ssn_id FROM $wpdb->sktn_klss_ssn AS s INNER JOIN $wpdb->splr_sktn_klss_ssn AS z ON z.sktn_klss_ssn_id = s.sktn_klss_ssn_id WHERE z.splr_id = $splr_id AND s.klss_ssn_id = $klss_ssn_id; " );
		} else {
			//Standardwerte
			$spieler          = null;
			$sktn_klss_ssn_id = null;
		}


		if ( ! $spieler && $splr_id ) {
			echo 'Error keine gültige Spieler ID';
		}

		echo '<tr>';
		echo '<td><label for="' . $post_splr_id . '">' . $sktn_klss_ssn_id . ' - ' . $spieler->vorname . ' ' . $spieler->nachname . '</label></td>';
		echo '<td>';
		echo '<input type="text" name="' . $post_splr_id . '" value="' . $splr_id . '"/>';
		echo '<input type="hidden" name="' . $post_sktn_klss_ssn_id . '" value="' . $sktn_klss_ssn_id . '">';
		echo '</td>';

		// Spalten fürs eintragen der Spiele
		$summe_spieler_ergebnisse = 0;
		for ( $i = 1; $i <= $klss_ssn->anz_spiele; ++ $i ) {
			echo '<td>';
			$post_ergebnis = 'spl[' . $j . '][ergebnis][' . $i . '][pins]';
			$ergebnis      = $_REQUEST['spl'][ $j ]['ergebnis'][ $i ]['pins'];
			echo '<input type="text" name="' . $post_ergebnis . '" value="' . $ergebnis . '" />';
			echo '</td>';
			$summe_spieler_ergebnisse += $ergebnis; //aufsummieren der Spiele pro Spieler
			$summe_spiel_ergebnisse[ $i ] += $ergebnis; //aufsummieren der Spiele pro Spielrunde
		}

		$summe_gesamt += $summe_spieler_ergebnisse; // Gesamtsumme ermitteln
		echo '<td>' . $summe_spieler_ergebnisse . '</td>';
		echo "</tr>";
	}
	?>
	<tr>
		<td></td>
		<td>Summe:</td>
		<?php foreach ( $summe_spiel_ergebnisse as $summe_spiel_ergebnis ) {
			echo '<td>' . $summe_spiel_ergebnis . '</td>';
		} ?>
		<td><?php echo $summe_gesamt; ?></td>
	</tr>

	<?php
	echo '</table>';
	echo '<input type="hidden" name="ergebnis" value="' . $summe_gesamt . '">';
	echo '<input type="hidden" name="runde" value="' . $runde . '">';
	echo '<input type="hidden" name="date" value="' . $date . '">';
	echo '<input type="hidden" name="klss_ssn_id" value="' . $klss_ssn_id . '">';
	echo '<input type="hidden" name="reserve" value="' . $reserve . '">';

	submit_button( "check", 'primary', 'action' );

	if ( 'check' == $action ) {
		echo '<p>Alles Überprüft?</p>';
		submit_button( "save", 'secondary', 'action' );
	}
	echo '</form>';

}

function bwdb_form_save() {
	global $wpdb;

	// Erstellt ein neues Array aus den Formulardaten zum eintragen via wpdb
	foreach ( $_POST['spl'] as $key => $val ) {
		$splr_id          = $val['splr_id'];
		$sktn_klss_ssn_id = $val['sktn_klss_ssn_id'];
		// nur eintragen wenn Spieler ID vorhanden und numerisch
		if ( ! empty( $splr_id ) && is_numeric( $splr_id ) ) {
			foreach ( $val['ergebnis'] as $spiel => $ergebnis ) {
				if ( ! empty( $ergebnis ) && $ergebnis['pins'] > 0 ) {   // ergebnis > 0 !
					$result = array(
						'splr_id'          => $splr_id,
						'nummer'           => $spiel,
						'ergebnis'         => $ergebnis['pins'],
						'sktn_klss_ssn_id' => $sktn_klss_ssn_id,
						'runde'            => $_POST['runde'],
						'date'             => $_POST['date'],
						'reserve'          => $_POST['reserve']
					);
					$wpdb->insert( $wpdb->spiel, $result );
					echo $wpdb->insert_id . '. Spiel Eingetragen! - Sektion ID: ' . $sktn_klss_ssn_id . ' Spieler ID:' . $splr_id . ' Runde: ' . $_POST['runde'] . ' Spiel: ' . $spiel . ' Ergebnis: ' . $ergebnis['pins'];

//						Fehlerüberprüfung:					
//						$wpdb->print_error();		
//						print_r($result);

					echo '</br >';
				}
			}
		}
	}
	$query_arg = array(
		'action'      => 'show',
		'klss_ssn_id' => $_POST['klss_ssn_id'],
		'date'        => $_POST['date'],
		'runde'       => $_POST['runde'],
		'reserve'     => $_POST['reserve']
	);
	echo '<a href="' . add_query_arg( $query_arg ) . '">NEXT</a>';
}

function bwdb_form_edit() {

	// @todo reorganize the whole insert thing to hand over $attr from the beginning ...
	$attr = shortcode_atts( array(
		'klss_ssn_id'      => '',   // for table layout (bad Design)
		'sktn_klss_ssn_id' => '',   // to get only on sktn_klss_ssn_id
		'reserve'          => '',    // including Reserve !
		'min'              => '1',        // Listet nur Spieler die gespielt haben ...
		'runde'            => '',
		'team'             => ''        // damit nix passiert bei der sQL abfrage ;)
	), $_GET );

	print_r( $attr );

	$data = bwdb_get_data( $attr );
	// array Umbau wieder unsauber dafür kann der Rest fürs erste so bleiben ...
	// Zuerst Sammeln wir je spieler die Daten und dann nummerierne wir das Array -> damits für die Ausgabe wieder passt ...
	foreach ( $data AS $value ) {
		$_REQUEST[ spl ][ $value->splr_id ][ splr_id ]                    = $value->splr_id;
		$_REQUEST[ spl ][ $value->splr_id ][ sktn_klss_ssn_id ]           = $value->sktn_klss_ssn_id;
		$_REQUEST[ spl ][ $value->splr_id ][ ergebnis ][ $value->nummer ] = $value->ergebnis;
	}

	$_REQUEST[ spl ] = array_values( $_REQUEST[ spl ] );
}


// Additional Work


// Experimental
function print_bwdb( $ar ) {
	echo "<pre>";
	print_r( $ar );
	echo "</pre>";

}

//für WP_List_Table experiment
// if ( ! is_admin() ) {
	//function get_current_screen() {
		//global $current_screen;
//
	//	if ( ! isset( $current_screen ) ) {
		//	return null;
		//}
//
	//	return $current_screen;
	//}
// }
?>