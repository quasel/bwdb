<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BWDB_List_Table_Spiele extends WP_List_Table {
	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'Spiel',     //singular name of the listed records
			'plural'   => 'Spiele',    //plural name of the listed records
			'ajax'     => false        //does this table support ajax?
		) );

	}

	function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />', //Render a checkbox instead of text
			'spl_id'   => 'ID',
			'splr_id'  => 'Passnr',
			'name'     => 'Spieler',
			'sektion'  => 'Sektion',
			'klasse'   => 'Klasse',
			'date'     => 'Datum',
			'runde'    => 'Runde',
			'nummer'   => 'Spiel',
			'ergebnis' => 'Ergebnis',
			'reserve'  => 'Reserve',
		);

		return $columns;
	}

	/* Default Ausgabe für nicht weiter definierte Spalten
	 * es erfolgt eine einfach unformatierte Ausgabe
	 */

	function column_default( $item, $column_name ) {
		if ( $item->$column_name ) {
			return $item->$column_name;
		}

		return ''; //print_r($item,true); //Show the whole array for troubleshooting purposes

	}

	function column_reserve( $item ) {
		if ( $item->reserve == '1' ) {
			return 'JA';
		} else {
			return '';
		}
	}

	function column_spl_id( $item ) {
		$actions = array(
			// @todo add_query_arg() benutzen ??
			// für die Zukunft:
			//'delete'    => sprintf('<a href="?page=%s&action=%s&spl_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->spl_id),
			'edit' => sprintf( '<a href="?page=%s&action=%s&spl_id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item->spl_id )

		);

		return sprintf( '%1$s %2$s', $item->spl_id, $this->row_actions( $actions ) );
	}

	function column_name( $item ) {
		return sprintf( '%1$s %2$s', $item->vorname, $item->nachname );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/
			$item->spl_id            //The value of the checkbox should be the record's id
		);
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			//true means its already sorted
			'splr_id' => array( 'splr_id', false ),
			'sektion' => array( 'sektion', false ),
			'sktn_id' => array( 'sktn_id', false ),
			'runde'   => array( 'runde', false )
		);

		return $sortable_columns;
	}


	function prepare_items() {
		global $wpdb;

		$per_page = 30;

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// @todo seeehhrrr unsauber !!!!
		// $_GET['nopaging']= 'false';
		$attr['paged']          = $this->get_pagenum();
		$attr['posts_per_page'] = $per_page;
		$attr['nopaging']       = '0';
		$attr['sktn_id']        = $_GET['sktn_id'];
		$attr['m']              = $_GET['m']; // Datum
		$attr['s']              = $_GET['s']; // searchterm
		$attr['exact']          = 'true'; // exacte suche
		$attr['min']            = '1';


		if ( empty( $_GET['orderby'] ) ) {
			$orderby = 'spl_id ';
		} else {
			$orderby = $_GET['orderby'] . ' ';
		}

		if ( empty( $_GET['order'] ) ) {
			$orderby .= 'desc';
		} else {
			$orderby .= $_GET['order'];
		}

		$ordery          .= ', runde DESC, nummer ASC';
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
				$this->months_dropdown();
				bwdb_dropdown( 'sektion', 'sktn_id' );
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

	/**
	 * Display a monthly dropdown for filtering items
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function months_dropdown( $post_type ) {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT YEAR( date ) AS year, MONTH( date ) AS month
			FROM $wpdb->spiel
			ORDER BY date DESC
		" ) );

		$month_count = count( $months );

		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
			return;
		}

		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
		?>
        <select name='m'>
            <option<?php selected( $m, 0 ); ?> value='0'><?php _e( 'Show all dates' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year ) {
					continue;
				}

				$month = zeroise( $arc_row->month, 2 );
				$year  = $arc_row->year;

				printf( "<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					/* translators: 1: month name, 2: 4-digit year */
					sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
				);
			}
			?>
        </select>
		<?php
	}

}

// now lets use it ;)
function bwdb_list_spiele() {
	global $wpdb;
// 	$form_data = shortcode_atts( array(
// 	        [splr_id] => 141
//            [vorname] => Christian
//            [nachname] => VODA
//            [verein] => A1 Telekom - Austria
//            [runde] => 0
//            [date] => 2012-09-14
//            [spl_id] => 205
//            [nummer] => 2
//            [sektion] => A1 Telekom - Austria 1
//            [reserve] => 0
//            [ergebnis] => 3
// 	

	switch ( $_REQUEST['action'] ) {

		case "edit":
			?>
            <h1>Korrektur</h1>
			<?php
			$attr['spl_id'] = $_REQUEST['spl_id'];
			$data           = bwdb_get_data( $attr );
			echo '<h3>Spieler: ' . $data[0]->vorname . ' ' . $data[0]->nachname . ' ' . $data[0]->sektion . '<br /> Runde: ' . $data[0]->runde . ' Spiel: ' . $data[0]->nummer . ' Datum: ' . $data[0]->date . '</h3>';

			if ( 1 == $data[0]->reserve ) {
				$checked = 'checked="checked"';
			}
			?>
            <div class="form-wrap">
                <form method="POST" action="<?php $_SERVER[ REQUEST_URI ] ?>">
                    <input type="hidden" name="action" value="update"/>

                    <div class="form-field form-required">
                        <label for="ergebnis">Ergebnis</label>
                        <input type="text" name="ergebnis" value="<?php echo $data[0]->ergebnis; ?>"/>
                    </div>
                    <div class="form-field form-required">
                        <label for="reserve">Reserve</label>
                        <input type="checkbox" name="reserve" size="10" value="1" <?php echo $checked; ?>">
                    </div>
					<?php submit_button( "Korrigieren", 'primary', 'button' ); ?>
                </form>
            </div>
			<?php
			break;
		case "update":
			$wpdb->update( $wpdb->spiel,
				array(
					'ergebnis' => $_REQUEST[ ergebnis ],
					'reserve'  => $_REQUEST[ reserve ]
				),
				array( 'spl_id' => $_REQUEST[ spl_id ] ),
				$format = null,
				$where_format = null );
			echo '<div class="updated"><p>Spiel korrigiert!</p></div>';
		default:
			//Create an instance of our package class...
			$bwdbListTable = new BWDB_List_Table_Spiele();
			// @debug:
			// print_array($_REQUEST);
			// Fetch, prepare, sort, and filter our data...
			$bwdbListTable->prepare_items();

			?>
            <div class="wrap">
                <h2>Liste aller Spiele</h2>
                <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
                <form id="bwdb-spiele" action='' method="get">
					<?php $bwdbListTable->search_box( 'search', 'spiel' ); ?>
                    <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

                    <!-- Now we can render the completed list table -->
					<?php $bwdbListTable->display() ?>
                </form>
            </div>
		<?php
	}
}

?>