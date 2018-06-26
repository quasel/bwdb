<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BWDB_List_Table_Vereine extends WP_List_Table {
	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'Verein',     //singular name of the listed records
			'plural'   => 'Vereine',    //plural name of the listed records
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
			$item->vrn_id               //The value of the checkbox should be the record's id
		);
	}

	function column_vrn_id( $item ) {
		$link = add_query_arg( array(
				'action' => 'edit',
				'vrn_id' => $item->vrn_id,
				'verein' => $item->verein
			)
		);

		$actions = array(
			// @todo add_query_arg() benutzen ??
			// für die Zukunft:
			//'delete'    => sprintf('<a href="?page=%s&action=%s&spr_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->vrn_id),
			'edit' => sprintf( '<a href="%s">Edit</a>', $link )
		);

		return sprintf( '%1$s %2$s', $item->vrn_id, $this->row_actions( $actions ) );
	}


	function get_columns() {
		$columns = array(
			'cb'     => '<input type="checkbox" />', //Render a checkbox instead of text
			'vrn_id' => 'ID',
			'verein' => 'Verein'
		);

		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			//true means its already sorted
			'vrn_id' => array( 'vrn_id', false ),
			'verein' => array( 'verein', true )
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
		$attr['s']              = $_GET['s']; // searchterm
		$attr['output']         = 'verein';

		if ( empty( $_GET['orderby'] ) ) {
			$orderby = 'verein ';
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
function bwdb_list_vereine() {
	global $wpdb;
	//Create an instance of our package class...
	$bwdbListTable = new BWDB_List_Table_Vereine();
	$base          = "admin.php?page=bwdb_vereine"; //todo - besseren Weg finden ...

	switch ( $bwdbListTable->current_action() ) {

		case "edit":
			//$attr['vrn_id'] = $_REQUEST['vrn_id'];
			//$attr['groupby'] = 'z.sktn_id';
			//$data=bwdb_get_data($attr);
			?>

			<?php
			$header = "Verein korrigieren";
			$action = "update";
			// falls korrektur der Vereins ID Sichern der alten :D
			$vrn_id_save = '<input type="hidden" name="vrn_id_save" value="' . $_REQUEST['vrn_id'] . '">';
			break;

		case "add":
			echo $wpdb->insert( $wpdb->verein,
				array(
					'vrn_id' => $_REQUEST['vrn_id'],
					'name'   => $_REQUEST['verein']
				)
			);


			$link = add_query_arg( 'message', 1 );
			wp_redirect( $link );
			exit;
			break;

		case "update":
			$wpdb->update( $wpdb->verein,
				array(
					'vrn_id' => $_REQUEST['vrn_id'],
					'name'   => $_REQUEST['verein']
				),
				array( 'vrn_id' => $_REQUEST['vrn_id_save'] ),
				$format = null,
				$where_format = null );

			$link = remove_query_arg( array( 'verein', 'vrn_id', 'action' ) );
			$link = add_query_arg( 'message', 3, $link );
			wp_redirect( $link );
			exit;
			break;

		default:
			$header = "Neuen Verein anlegen";
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

	?>
    <div class="wrap nosubsub">
        <h2>Verein - bearbeiten und erstellen</h2>
		<?php if ( isset( $_REQUEST['message'] ) && ( $msg = (int) $_REQUEST['message'] ) ) : ?>
            <div id="message" class="updated"><p><?php echo $messages[ $msg ]; ?></p></div>
			<?php $_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] );
		endif; ?>

        <br class="clear"/>

        <div id="col-container">
            <div id="col-right">
                <div class="col-wrap">
                    <!--  @todo Vereinssuche
		//	<form class="search-form" action="" method="get">
		//	<?php $bwdbListTable->search_box( 'search', 'spieler' ); ?>
		//	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" 
		//	</form>  -->
                    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
                    <form id="bwdb-vereine" action='' method="get">
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
							<?php echo $vrn_id_save; ?>
                            <div class="form-field form-required">
                                <label for="vrn_id">Verein ID</label>
                                <input type="text" readonly="readonly" name="vrn_id"
                                       value="<?php echo $_REQUEST[ vrn_id ] ?>"/>
                            </div>

                            <div class="form-field form-required">
                                <label for="verein">Name</label>
                                <input type="text" name="verein" value="<?php echo $_REQUEST[ verein ]; ?>"/>
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