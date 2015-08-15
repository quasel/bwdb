<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BWDB_List_Table_Klassen extends WP_List_Table {
	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'Klasse',     //singular name of the listed records
			'plural'   => 'Klassen',    //plural name of the listed records
			'ajax'     => false        //does this table support ajax?
		) );

	}

	function column_default( $item, $column_name ) {
		if ( $item->$column_name ) {
			return $item->$column_name;
		}

		return ''; //print_r($item,true); //Show the whole array for troubleshooting purposes
	}


	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/
			$item->klss_id               //The value of the checkbox should be the record's id
		);
	}

	function column_klss_id( $item ) {
		$link = add_query_arg( array(
				'action'      => 'edit',
				'klss_id'     => $item->klss_id,
				'klasse'      => $item->klasse,
				'anz_runden'  => $item->anz_runden,
				'anz_spiele'  => $item->anz_spiele,
				'max_spieler' => $item->max_spieler
			)
		);

		$actions = array(
			// @todo add_query_arg() benutzen ??
			// für die Zukunft:
			//'delete'    => sprintf('<a href="?page=%s&action=%s&spr_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->klss_id),
			'edit' => sprintf( '<a href="%s">Edit</a>', $link )
		);

		return sprintf( '%1$s %2$s', $item->klss_id, $this->row_actions( $actions ) );
	}


	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />', //Render a checkbox instead of text
			'klss_id'     => 'ID',
			'saison'      => 'Saison',
			'bewerb'      => 'Bewerb',
			'klasse'      => 'Klasse',
			'anz_runden'  => 'Runden',
			'anz_spiele'  => 'Spiele',
			'max_spieler' => 'Max Spieler'
		);

		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			//true means its already sorted
			'klss_id' => array( 'klss_id', true ),
			'klasse'  => array( 'klasse', false ),
			'bewerb'  => array( 'bewerb', false ),
			'saison'  => array( 'saison', false )
		);

		return $sortable_columns;
	}

	function prepare_items() {
		global $wpdb;

		$per_page = 50;

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// @todo seeehhrrr unsauber !!!!
		// $_GET['nopaging']= 'false';
		$attr['paged']          = $this->get_pagenum();
		$attr['posts_per_page'] = $per_page;
		$attr['nopaging']       = '0';
		$attr['bwrb_id']        = $_GET['bwrb_id'];
		$attr['ssn_id']         = $_GET['ssn_id'];
		$attr['s']              = $_GET['s']; // searchterm
		$attr['output']         = 'klss_ssn';

		if ( empty( $_GET['orderby'] ) ) {
			$orderby = 'klasse ';
		} else {
			$orderby = $_GET['orderby'] . ' ';
		}

		if ( empty( $_GET['order'] ) ) {
			$orderby .= 'asc';
		} else {
			$orderby .= $_GET['order'];
		}

		$attr['orderby'] = $orderby;

		$this->items = bwdb_get_data( $attr );

		// @todo - alt wozu? relikt? WTF?
		// $result = $wpdb->get_results("SELECT klss_id, name as klasse, anz_runden, anz_spiele, max_spieler FROM $wpdb->klasse ORDER BY $orderby");

		$total_items = $wpdb->get_var( "SELECT FOUND_ROWS()" );
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page'    => $per_page
		) );
	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @param string $which , helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {
		?>
		<div class="alignleft actions">
			<?php

			if ( 'top' == $which ) {
				//The code that goes before the table is here
				echo '<span<>Saison: </span>';
				bwdb_dropdown( 'saison', 'ssn_id' );
				echo '<span>Bewerb: </span>';
				bwdb_dropdown( 'bewerb', 'bwrb_id' );
				// Notwendig ? Kopiert ...
				do_action( 'restrict_manage_posts' );
				submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) );

			}
			if ( 'bottom' == $which ) {
				//The code that goes after the table is there
				echo "Hi, I'm after the table";
			}
			?>
		</div>
		<?php
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);

		return $actions;
	}


}

// now lets use it ;)
function bwdb_list_klassen() {
	global $wpdb;
	//Create an instance of our package class...
	$bwdbListTable = new BWDB_List_Table_Klassen();
	$base          = "admin.php?page=bwdb_klassen"; //todo - besseren Weg finden ...

	switch ( $bwdbListTable->current_action() ) {

		case "edit":
			//$attr['klss_id'] = $_REQUEST['klss_id'];
			//$attr['groupby'] = 'z.sktn_id';
			//$data=bwdb_get_data($attr);
			?>

			<?php
			$header = "Klasse korrigieren";
			$action = "update";
			// falls korrektur der Klasses ID Sichern der alten :D
			$klss_id_save = '<input type="hidden" name="klss_id_save" value="' . $_REQUEST['klss_id'] . '">';
			break;

		case "add":
			echo $wpdb->insert( $wpdb->klasse,
				array(
					'klss_id'     => $_REQUEST['klss_id'],
					'name'        => $_REQUEST['klasse'],
					'anz_runden'  => $_REQUEST['anz_runden'],
					'anz_spiele'  => $_REQUEST['anz_spiele'],
					'max_spieler' => $_REQUEST['max_spieler']
				)
			);


			$link = add_query_arg( 'message', 1 );
			wp_redirect( $link );
			exit;
			break;

		case "update":
			$wpdb->update( $wpdb->klasse,
				array(
					'klss_id'     => $_REQUEST['klss_id'],
					'name'        => $_REQUEST['klasse'],
					'anz_runden'  => $_REQUEST['anz_runden'],
					'anz_spiele'  => $_REQUEST['anz_spiele'],
					'max_spieler' => $_REQUEST['max_spieler']
				),
				array( 'klss_id' => $_REQUEST['klss_id_save'] ),
				$format = null,
				$where_format = null );

			$link = remove_query_arg( array(
				'klasse',
				'klss_id',
				'action',
				'runden',
				'anz_runden',
				'max_spieler',
				'anz_spiele'
			) );
			$link = add_query_arg( 'message', 3, $link );
			wp_redirect( $link );
			exit;
			break;

		default:
			$header = "Neuen Klasse anlegen";
			$action = "add";
			// @todo - verstehen warum wordpress das so macht ;)
			if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
				$_SERVER['REQUEST_URI'] = remove_query_arg( array(
					'_wp_http_referer',
					'_wpnonce'
				), stripslashes( $_SERVER['REQUEST_URI'] ) );
			}


	}
	$messages[1] = __( 'Item added.' );
	$messages[2] = __( 'Item deleted.' );
	$messages[3] = __( 'Item updated.' );
	$messages[4] = __( 'Item not added.' );
	$messages[5] = __( 'Item not updated.' );
	$messages[6] = __( 'Items deleted.' );


	// Fetch, prepare, sort, and filter our data...
	$bwdbListTable->prepare_items();

	// @debug:
	//	echo '<br />$_REQUEST =';
	//	print_r($_REQUEST);
	//	echo '<br />$data =';
	//	print_r($data);
	?>
	<div class="wrap nosubsub">
		<h2>Klasse - bearbeiten und erstellen</h2>
		<?php if ( isset( $_REQUEST['message'] ) && ( $msg = (int) $_REQUEST['message'] ) ) : ?>
			<div id="message" class="updated"><p><?php echo $messages[ $msg ]; ?></p></div>
			<?php $_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] );
		endif; ?>

		<br class="clear"/>

		<div id="col-container">
			<div id="col-right">
				<div class="col-wrap">
					<!--  @todo Klassessuche
		//	<form class="search-form" action="" method="get">
		//	<?php $bwdbListTable->search_box( 'search', 'spieler' ); ?>
		//	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" 
		//	</form>  -->
					<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
					<form id="bwdb-klassen" action='' method="get">
						<!-- For plugins, we also need to ensure that the form posts back to our current page -->
						<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
						<!-- Now we can render the completed list table -->
						<?php $bwdbListTable->display() ?>
						<br class="clear"/>
					</form>
				</div>

			</div>
			<!-- /col-right -->


			<div id="col-left">
				<div class="col-wrap">
					<div class="form-wrap">
						<h3><?php echo $header; ?></h3>

						<form id="addtag" class="validate" method="post" action="<?php $_SERVER[ REQUEST_URI ] ?>">
							<input type="hidden" name="action" value="<?php echo $action; ?>"/>
							<?php echo $klss_id_save; ?>
							<div class="form-field form-required">
								<label for="klss_id">Klasse ID</label>
								<input type="text" readonly="readonly" name="klss_id"
								       value="<?php echo $_REQUEST[ klss_id ] ?>"/>
							</div>
							<div class="form-field form-required">
								<label for="klasse">Name</label>
								<input type="text" name="klasse" value="<?php echo $_REQUEST[ klasse ]; ?>"/>
							</div>
							<div class="form-field form-required">
								<label for="anz_runden">Anzahl Runden </label>
								<input type="text" name="anz_runden" value="<?php echo $_REQUEST[ anz_runden ]; ?>"/>
							</div>
							<div class="form-field form-required">
								<label for="anz_spiele">Anzahl Spiele </label>
								<input type="text" name="anz_spiele" value="<?php echo $_REQUEST[ anz_spiele ]; ?>"/>
							</div>
							<div class="form-field form-required">
								<label for="max_spieler">Maximal erlaubte Spieler </label>
								<input type="text" name="max_spieler" value="<?php echo $_REQUEST[ max_spieler ]; ?>"/>
							</div>
							<?php submit_button( "Speichern", 'primary', 'button' ); ?>

						</form>
					</div>

				</div>
			</div>
			<!-- /col-left -->

		</div>
		<!-- /col-container -->
	</div><!-- /wrap -->

	<?php
}

?>