<?php

if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/* Marc Perel - bwdb Twitter List Setup Code */
/**
 * creates all tables for the gallery
 * called during register_activation hook
 *
 * @access internal
 * @return void
 */

function bwdb_setup_db() {
	//NB Always set wpdb globally!
	global $wpdb, $wp_roles, $wp_version;

	// Check for capability
//	if ( !current_user_can('activate_plugins') )
//		wp_die( __( 'Cheatin&#8217; uh?' ) );
//		return;

//	 Set the capabilities for the administrator
//	$role = get_role('administrator');
//	 We need this role, no other chance
//	if ( empty($role) ) {
//		update_option( "ngg_init_check", __('Sorry, NextGEN Gallery works only with a role called administrator',"nggallery") );
//		return;
//	}

	// upgrade function changed in WordPress 2.3 - needed for dbDelta()
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


	// add charset & collate like wp core
	$charset_collate = '';

	if ( version_compare( mysql_get_server_info(), '4.1.0', '>=' ) ) {
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}


	//Set Table Name
	$bwdb_klasse    = $wpdb->prefix . 'bwdb_klasse';
	$bwdb_sektion   = $wpdb->prefix . 'bwdb_sektion';
	$bwdb_spiel     = $wpdb->prefix . 'bwdb_spiel';
	$bwdb_spieler   = $wpdb->prefix . 'bwdb_spieler';
	$bwdb_splr_sktn = $wpdb->prefix . 'bwdb_splr_sktn';
	$bwdb_verein    = $wpdb->prefix . 'bwdb_verein';


	// Create the main Table, don't forget the ( ` ) - MySQL Reference @ http://www.w3schools.com/Sql/sql_create_table.asp
	// could be case senstive : http://dev.mysql.com/doc/refman/5.1/en/identifier-case-sensitivity.html


	if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$bwdb_klasse'" ) ) {

		$sql = "CREATE TABLE " . $bwdb_klasse . " (
		klss_id BIGINT(20) NOT NULL AUTO_INCREMENT ,
		name VARCHAR(255) NOT NULL ,
		anz_runden BIGINT(20) NOT NULL ,
		anz_spiele	BIGINT(20) NOT NULL ,
		max_spieler BIGINT(20) NOT NULL ,
		korr VARCHAR(255) NOT NULL ,
		stamp DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY klss_id (klss_id)
		) $charset_collate;";

		dbDelta( $sql );
	}


	if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$bwdb_sektion'" ) ) {

		$sql = "CREATE TABLE " . $bwdb_sektion . " (
		sktn_id BIGINT(20) NOT NULL AUTO_INCREMENT ,
        name VARCHAR(255) NOT NULL ,
		vrn_id BIGINT(20) DEFAULT '0' NOT NULL ,
		klss_id BIGINT(20) DEFAULT '0' NOT NULL ,
		korr VARCHAR(255) NOT NULL ,
		stamp DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY sktn_id (sktn_id),
		KEY vrn_id (vrn_id),
		KEY klss_id (klss_id)
		) $charset_collate;";

		dbDelta( $sql );
	}

	if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$bwdb_spiel'" ) ) {

		$sql = "CREATE TABLE " . $bwdb_spiel . " (
		spl_id BIGINT(20) NOT NULL AUTO_INCREMENT ,
		splr_id BIGINT(20) NOT NULL ,
		sktn_id BIGINT(20) NOT NULL ,
		runde BIGINT(20) NOT NULL ,
		date DATE NOT NULL DEFAULT '0000-00-00',
		nummer BIGINT(20) NOT NULL ,
		ergebnis BIGINT(20) NOT NULL ,
		splr_id_gegner BIGINT(20) ,
		reserve TINYINT NOT NULL DEFAULT '0' ,
		korr VARCHAR(255) NOT NULL ,
		stamp DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY spl_id (spl_id)
		) $charset_collate;";

		dbDelta( $sql );
	}


	if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$bwdb_spieler'" ) ) {

		$sql = "CREATE TABLE " . $bwdb_spieler . " (
		splr_id BIGINT(20) NOT NULL AUTO_INCREMENT ,
        vorname VARCHAR(255) NOT NULL ,
        nachname VARCHAR(255) NOT NULL ,
		vrn_id BIGINT(20) DEFAULT '0' NOT NULL ,
		sex TINYINT NOT NULL DEFAULT '2' ,
		korr VARCHAR(255) NOT NULL ,
		stamp DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY splr_id (splr_id)
		) $charset_collate;";

		dbDelta( $sql );
	}


	if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$bwdb_splr_sktn'" ) ) {

		$sql = "CREATE TABLE " . $bwdb_splr_sktn . " (
		sktn_id BIGINT(20) NOT NULL ,
		splr_id BIGINT(20) NOT NULL ,
		korr VARCHAR(255) NOT NULL ,
		stamp DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (sktn_id, splr_id)
		) $charset_collate;";

		dbDelta( $sql );
	}

	if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$bwdb_verein'" ) ) {

		$sql = "CREATE TABLE " . $bwdb_verein . " (
		vrn_id BIGINT(20) NOT NULL AUTO_INCREMENT ,
        name VARCHAR(255) NOT NULL ,
		korr VARCHAR(255) NOT NULL ,
		stamp DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY vrn_id (vrn_id)
		) $charset_collate;";

		dbDelta( $sql );
	}

	// check one table again, to be sure
//	if( !$wpdb->get_var( "SHOW TABLES LIKE '$bwdb_bewerb'" ) ) {
//		update_option( "ngg_init_check", __('NextGEN Gallery : Tables could not created, please check your database settings',"nggallery") );
//		return;
//	}	


	// los gehts

}

?>