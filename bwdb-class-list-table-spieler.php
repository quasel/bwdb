<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class BWDB_List_Table_Spieler extends WP_List_Table
{
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct()
    {
        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'Spieler',     //singular name of the listed records
            'plural' => 'Spieler',    //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));

    }

    function column_default($item, $column_name)
    {
        if ($item->$column_name) {
            return $item->$column_name;
        }
        return ''; //print_r($item,true); //Show the whole array for troubleshooting purposes
    }

    function column_sex($item)
    {
        if ($item->sex == '1') {
            return 'M';
        } else return 'F';
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item->splr_id               //The value of the checkbox should be the record's id
        );
    }

    function column_splr_id($item)
    {
        $link = add_query_arg(array('action' => 'edit',
                'splr_id' => $item->splr_id,
                'vorname' => $item->vorname,
                'nachname' => $item->nachname,
                'vrn_id' => $item->vrn_id,
                'sex' => $item->sex)
        );

        $actions = array(
            // @todo add_query_arg() benutzen ??
            // für die Zukunft:
            //'delete'    => sprintf('<a href="?page=%s&action=%s&spr_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->splr_id),
            'edit' => sprintf('<a href="%s">Edit</a>', $link)
        );
        return sprintf('%1$s %2$s', $item->splr_id, $this->row_actions($actions));
    }


    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'splr_id' => 'ID',
            'vorname' => 'Vorname',
            'nachname' => 'Nachname',
            'verein' => 'Verein',
            'sex' => 'Geschlecht'
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            //true means its already sorted
            'splr_id' => array('splr_id', false),
            'verein' => array('verein', false),
            'nachname' => array('nachname', true)
        );
        return $sortable_columns;
    }

    function prepare_items()
    {
        global $wpdb;

        $per_page = 50;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // @todo seeehhrrr unsauber !!!!
        // $_GET['nopaging']= 'false';
        $attr['paged'] = $this->get_pagenum();
        $attr['posts_per_page'] = $per_page;
        $attr['nopaging'] = '0';
        $attr['vrn_id'] = $_GET['vrn_id'];
        $attr['sktn_klss_ssn_id'] = $_GET['sktn_klss_ssn_id'];
        $attr['s'] = $_GET['s']; // searchterm
        $attr['groupby'] = 'p.splr_id';
        $attr['output'] = 'spieler';

        if (empty($_GET['orderby'])) {
            $orderby = 'nachname ';
        } else {
            $orderby = $_GET['orderby'] . ' ';
        }

        if (empty($_GET['order'])) {
            $orderby .= 'asc';
        } else {
            $orderby .= $_GET['order'];
        }

        $attr['orderby'] = $orderby;

        $this->items = bwdb_get_data($attr);
// @debug
//    		echo '<pre>';
//	        print_r($this->items);
//	        print_r($wpdb->last_query);
//	        echo '</pre>';
        $total_items = $wpdb->get_var("SELECT FOUND_ROWS()");
        $total_pages = ceil($total_items / $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'per_page' => $per_page
        ));
    }

    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which , helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav($which)
    {
        ?>
        <div class="alignleft actions">
            <?php

            if ('top' == $which) {
                //The code that goes before the table is here
                echo '<span<>Verein: </span>';
                bwdb_dropdown('verein', 'vrn_id');
                echo '<span>Sektion: </span>';
                if (!empty($_REQUEST['vrn_id'])) {
                    bwdb_dropdown('sktn_klss_ssn', 'sktn_klss_ssn_id', array('vrn_id' => $_REQUEST['vrn_id']));
                } else {
                    echo '<span>Bitte Verein wählen</span>';
                }
                // Notwendig ? Kopiert ...
                do_action('restrict_manage_posts');
                submit_button(__('Filter'), 'secondary', false, false, array('id' => 'post-query-submit'));

            }
            if ('bottom' == $which) {
                //The code that goes after the table is there
                echo "Hi, I'm after the table";
            }
            ?>
        </div>
    <?php
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete',
            'sektion' => 'Sektion zuweisen'
        );
        return $actions;
    }


}

// now lets use it ;)
function bwdb_list_spieler()
{
    global $wpdb;
    //Create an instance of our package class...
    $bwdbListTable = new BWDB_List_Table_Spieler();
    $base = "admin.php?page=bwdb_spieler"; //todo - besseren Weg finden ...

    switch ($bwdbListTable->current_action()) {

        case "edit":
            //$attr['splr_id'] = $_REQUEST['splr_id'];
            //$attr['groupby'] = 'z.sktn_klss_ssn_id';
            //$data=bwdb_get_data($attr);
            ?>

            <?php
            $header = "Spieler korrigieren";
            $action = "update";
            // falls korrektur der Spieler ID Sichern der alten :D
            $splr_id_save = '<input type="hidden" name="splr_id_save" value="' . $_REQUEST['splr_id'] . '">';
            break;

        case "add":
            $wpdb->insert($wpdb->spieler,
                array('splr_id' => $_REQUEST['splr_id'],
                    'vorname' => $_REQUEST['vorname'],
                    'nachname' => $_REQUEST['nachname'],
                    'sex' => $_REQUEST['sex'],
                    'vrn_id' => $_REQUEST['vrn_id'])
            );


            $link = add_query_arg('message', 1);


            // @todo selber code bei add & update und ähnlicher bei save_sktn - optimieren !!!!
            foreach ($_REQUEST['sektionen'] as $sektion) {
                $wpdb->query(
                    $wpdb->prepare("
						REPLACE INTO $wpdb->splr_sktn_klss_ssn (sktn_klss_ssn_id, splr_id) VALUES ( %d, %d)",
                        $sektion, $_REQUEST['splr_id']
                    )
                );
            }
            wp_redirect($link);
            exit;
            break;

        case "update":
            $wpdb->update($wpdb->spieler,
                array('splr_id' => $_REQUEST['splr_id'],
                    'vorname' => $_REQUEST['vorname'],
                    'nachname' => $_REQUEST['nachname'],
                    'vrn_id' => $_REQUEST['vrn_id'],
                    'sex' => $_REQUEST['sex']),
                array('splr_id' => $_REQUEST['splr_id_save']),
                $format = null,
                $where_format = null);

            //löscht alle zuordnungen Spieler/Sektion
            $wpdb->query(
                $wpdb->prepare("
						DELETE FROM $wpdb->splr_sktn_klss_ssn WHERE splr_id = %s",
                    $_REQUEST['splr_id']
                )
            );

            // @todo selber code bei add & update und ähnlicher bei save_sktn - optimieren !!!!
            foreach ($_REQUEST['sektionen'] as $sektion) {
                $wpdb->query(
                    $wpdb->prepare("
							REPLACE INTO $wpdb->splr_sktn_klss_ssn (sktn_klss_ssn_id, splr_id) VALUES ( %d, %d)",
                        $sektion, $_REQUEST['splr_id']
                    )
                );
            }

            $link = remove_query_arg(array('vorname', 'nachname', 'splr_id', 'action', 'sex'));
            $link = add_query_arg('message', 3, $link);
            wp_redirect($link);
            exit;
            break;

        case "sektion":
            ?>
            <h2>Einer Sektion mehrere Spieler zuweisen</h2>
            <div class="form-wrap">
                <form id="addtag" class="validate" method="post" action="<?php $_SERVER[REQUEST_URI] ?>">
                    <input type="hidden" name="action" value="save_sktn"/>
                    <?php foreach ($_REQUEST['spieler'] as $spieler) {
                        echo '<input type="hidden" name="spieler[]" value="' . $spieler . '"/>';
                    }
                    ?>
                    <div class="form-field form-required">
                        <?php bwdb_dropdown('sektion', 'sktn_klss_ssn_id', array('vrn_id' => $_REQUEST['vrn_id'])); ?>
                    </div>
                    <?php submit_button("Speichern", 'primary', 'button'); ?>
                </form>
            </div>
            <?php
            break;

        case "save_sktn":
            // @todo selber code bei add & update und ähnlicher bei save_sktn (nur verdreht einmal allen Spielern eine Sektion und dann einem Spieler ein paar Sektionen...- optimieren !!!!
            foreach ($_REQUEST['spieler'] as $spieler) {
                $wpdb->query(
                    $wpdb->prepare("
						REPLACE INTO $wpdb->splr_sktn_klss_ssn (sktn_klss_ssn_id, splr_id) VALUES ( %d, %d)",
                        $_REQUEST['sktn_klss_ssn_id'], $spieler
                    )
                );
            }
            $link = add_query_arg(array('message' => 3,
                'vrn_id' => $_REQUEST['vrn_id']
            ), $base);
            wp_redirect($link);
            exit;
            break;

        default:
            $header = "Neuen Spieler anlegen";
            $action = "add";
            // @todo - verstehen warum wordpress das so macht ;)
            if (!empty($_REQUEST['_wp_http_referer'])) {
                $_SERVER['REQUEST_URI'] = remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI']));
            }


    }
    $messages[1] = __('Item added.');
    $messages[2] = __('Item deleted.');
    $messages[3] = __('Item updated.');
    $messages[4] = __('Item not added.');
    $messages[5] = __('Item not updated.');
    $messages[6] = __('Items deleted.');


    // Fetch, prepare, sort, and filter our data...
    $bwdbListTable->prepare_items();

    // @debug:
    //	echo '<br />$_REQUEST =';
    //	print_r($_REQUEST);
    //	echo '<br />$data =';
    //	print_r($data);
    ?>
    <div class="wrap nosubsub">
        <h2>Spieler - bearbeiten und erstellen</h2>
        <?php if (isset($_REQUEST['message']) && ($msg = (int)$_REQUEST['message'])) : ?>
            <div id="message" class="updated"><p><?php echo $messages[$msg]; ?></p></div>
            <?php $_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
        endif; ?>
        <p> ACHTUNG:<br/>Ist die Auswahl auf einen Verein eingeschränkt, <br/>so darf beim Spieler anlegen der Verein
            nicht geändert werden.</p>

        <p> HINWEIS:<br/>Ist die Auswahl auf eine Sektion eingeschränkt <br/>und es wird der Verein verändert, so muß
            "Auswahl einschränken" <br/>2x gedrückt werden zum aktualisieren.</p>


        <br class="clear"/>

        <div id="col-container">
            <div id="col-right">
                <div class="col-wrap">
                    <form class="search-form" action="" method="get">
                        <?php $bwdbListTable->search_box('search', 'spieler'); ?>
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"
                    </form>
                    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
                    <form id="bwdb-spieler" action='' method="get">
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

                        <form id="addtag" class="validate" method="post" action="<?php $_SERVER[REQUEST_URI] ?>">
                            <input type="hidden" name="action" value="<?php echo $action; ?>"/>
                            <?php echo $splr_id_save; /* hidden field with old id */ ?>
                            <div class="form-field form-required">
                                <label for="splr_id">Spieler ID</label>
                                <input type="text" name="splr_id" value="<?php echo $_REQUEST[splr_id] ?>"/>
                            </div>

                            <div class="form-field form-required">
                                <label for="vorname">Vorname</label>
                                <input type="text" name="vorname" value="<?php echo $_REQUEST[vorname]; ?>"/>
                            </div>
                            <div class="form-field form-required">
                                <label for="nachname">Nachname</label>
                                <input type="text" name="nachname" value="<?php echo $_REQUEST[nachname]; ?>"/>
                            </div>
                            <div class="form-field form-required">
                                <label for="sex">Geschlecht</label>
                                <select name="sex" size="1">
                                    <option<?php selected($_REQUEST['sex'], '2'); ?>
                                        value='2'><?php _e('Show all'); ?></option>
                                    <option<?php selected($_REQUEST['sex'], '0'); ?> value="0">Frau</option>
                                    <option<?php selected($_REQUEST['sex'], '1'); ?> value="1">Mann</option>
                                </select>
                            </div>
                            <div class="form-field form-required">
                                <label for="verein">Verein</label>
                                <?php bwdb_dropdown('verein', 'vrn_id'); ?>
                            </div>
                            <div class="form-checkbox">
                                <?php // wenn eine vrn_id vorhanden ist - werden die Sektions Checkboxen angezeigt !
                                if (!empty($_REQUEST[vrn_id])) {
                                    echo '<p>Sektion</p>';
                                    $attr = array();
                                    $attr['vrn_id'] = $_GET['vrn_id'];
                                    $attr['groupby'] = 's.sktn_klss_ssn_id';
                                    $attr['orderby'] = 'k.klss_id';
                                    $attr['output'] = 'sktn_klss_ssn';
                                    $sktn_vrn = bwdb_get_data($attr);

                                    foreach ($sktn_vrn as $sktn_v) {
                                        if (!empty($sktn_v->sktn_klss_ssn_id)) { // los gehts aber nur wenns eine skt_id gibt - da wir über Spieler joinen gäbs sonst auch eine '' Sektion ...
                                            $sktn_klss_ssn_id = '0';

                                            if (!empty($_REQUEST[splr_id])) { // nur Sektionen suchen wenns auch einen Spieler gibt sonst brauchts keine checked boxen ...
                                                $attr['splr_id'] = $_GET['splr_id'];
                                                $attr['output'] = 'spieler';
                                                $sktn_splr = bwdb_get_data($attr);
                                                foreach ($sktn_splr as $sktn_s) {
                                                    if ($sktn_v->sktn_klss_ssn_id == $sktn_s->sktn_klss_ssn_id) $sktn_klss_ssn_id = $sktn_v->sktn_klss_ssn_id; // für die auswahl checked ....
                                                }
                                            }
                                            $label = 'sektion_' . $sktn_v->sktn_klss_ssn_id;
                                            printf('<label for="%s"><input id="%s"type="checkbox" name="sektionen[]" value="%s" %s> %s - %s / %s  </label>',
                                                $label,
                                                $label,
                                                $sktn_v->sktn_klss_ssn_id,
                                                checked($sktn_klss_ssn_id, $sktn_v->sktn_klss_ssn_id, false),
                                                $sktn_v->saison,
                                                $sktn_v->sektion,
                                                $sktn_v->klasse
                                            );
                                        }
                                    }
                                }
                                ?>
                            </div>
                            <?php submit_button("Speichern", 'primary', 'button'); ?>

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