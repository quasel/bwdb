<?php
/* Marc Perel - bwdb Twitter List Add/Edit Form*/

//NB Always set wpdb globally!
global $wpdb, $table_Details;

//Get the Twitter Contact's Details
$twitter_table = $wpdb->prefix . "bwdb_twitter_list";
//Create SQL
$use_sql = "SELECT * FROM " . $twitter_table . " WHERE  keyId = " . $_GET['id'];
//Run Query
$table_Details = $wpdb->get_results( $use_sql );
?>
<h1>My Twirectory Management</h1>
<!-- Breadcrumb -->
<p><a href="<?php echo get_option( 'siteurl' ) . "/wp-admin/edit.php?page=bwdb-twitter-list/bwdb-twitter-list.php"; ?>">Twitter
		Directory</a> &raquo; Edit <?php echo $table_Details[0]->name; ?></p>

<!-- Post to bwdb-twitter-list.php NOT straight to *-update.php -->
<form
	action="<?php echo get_option( 'siteurl' ) . '/wp-admin/admin.php?page=bwdb-twitter-list/bwdb-twitter-list.php'; ?>"
	id="bwdb-gallery-form" method="post" enctype="multipart/form-data">
	<div class="postbox">
		<table>
			<tr>
				<th><label>Twitter Name</label></th>
				<th><label>Full Name</label></th>
			</tr>
			<tr>
				<!-- If Querying One record, it's pointless using a foreach loop, just say $array[0] and you access the first detail in the row -->
				<td><input type="text" name="username" value="<?php echo $table_Details[0]->username; ?>"/></td>
				<td><input type="text" name="name" value="<?php echo $table_Details[0]->name; ?>"/></td>
			</tr>
		</table>
	</div>
	<!-- If we're editing a user, put the "edit_item" input here -->
	<?php if ( $_GET["id"] !== "" ) : ?>
		<input type="hidden" name="edit_item" value="<?php echo $table_Details[0]->menuId; ?>"/>
	<?php endif; ?>
	<!-- This tells bwdb-twitter-list.php to run the update function() -->
	<input type="hidden" name="bwdb_twitter_list_update" value="1"/>

	<!--  SAVE BUTTON HERE -->
	<p class="submit"><input type="submit" value="save"/></p>
</form>

