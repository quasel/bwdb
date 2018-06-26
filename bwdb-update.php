<?php
/* Marc Perel - bwdb Twitter List Update Code */
function bwdb_twitter_list_update() {
	//NB Always set wpdb globally!
	global $wpdb;
	$twitter_table = $wpdb->prefix . "bwdb_twitter_list";

	if ( ! ( $_POST["edit_item"] ) ) :
		// Create Insert SQL and Insert the new Data
		$insert_sql = "
					INSERT INTO " . $twitter_table . "
						(`username`,`name`)
					VALUES
						('" . $_POST["name"] . "','" . $_POST["username"] . "')";

		/* Redirect - Remember don't post straight to this page, rather call the function so that your redirect works*/
		if ( ! $wpdb->query( $insert_sql ) === true ) :
			wp_redirect( "admin.php?page=bwdb-twitter-list/bwdb-twitter-list.php&no_save=1" );
		else :
			wp_redirect( "admin.php?page=bwdb-twitter-list/bwdb-twitter-list.php&changes_done=1" );
		endif;
	else :
		// Create Update SQL and Insert the new Data
		$update_sql = "UPDATE " . $twitter_table . "
							SET `username` = '" . $_POST["username"] . "',
								`name` = '" . $_POST["name"] . "'
							WHERE keyId = " . $_POST["edit_item"];

		/* Redirect - Remember don't post straight to this page, rather call a function so that your redirect works*/
		if ( ! $wpdb->query( $insert_sql ) === true ) :
			wp_redirect( "admin.php?page=bwdb-twitter-list/bwdb-twitter-list.php&no_save=1" );
		else :
			wp_redirect( "admin.php?page=bwdb-twitter-list/bwdb-twitter-list.php&changes_done=1" );
		endif;
	endif;
}

?>