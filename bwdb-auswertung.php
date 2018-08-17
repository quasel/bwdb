<?php

/* BWDB Auswertung */
function bwdb_auswertung() {
	global $BwDb_shortcodes;
	//NB Always set wpdb globally!
	global $wpdb;

	?>
    <h1>Spielwiese...</h1>
    <hr>
	<?php
	// Aufruf
	// BwDb_shortcodes::bwdbShow( array ( sktn_klss_ssn_id => '6', show => 'sktn_klss_ssn') );
	// BwDb_shortcodes::bwdbShow( array ( vrn_id => '1', show => 'verein') );
	//alternativ
	// do_shortcode("[bwdb sktn_klss_ssn_id=6, show=sktn_klss_ssn]");
	// immer mindestens einen Parameter angeben sonst funktioniert array_merge nicht ....
	// Achtung Werte im shortcode können das Ergebnis unerwartet verfälschen da $_GET ausgelesen wird und
	// priorisiert wird ...
	do_shortcode( '[bwdb show=schnitt]' );

	// @todo - Doppel-Liste wie Partsch!!!!!!
	// @todo - DB - Name ändern in verein ? -> natural joins ....

	?>
    <hr>
	<?php
}

function bwdbShowAvg( $attr ) {
// @todo:  $attr durchgängi nutzen nicht mal aus dem Request und mal so möglich?

	// home_url() = $_SERVER['REQUEST'];
	// home_url() = home_url();

	if ( ! isset( $attr['ssn_id'] ) ) {
		$attr['ssn_id'] = $_REQUEST['ssn_id'];
	}

	/*	// Ausgabe der Saisonen in einer ul - die aktuelle Saison erhält die klasse: active
		echo '<div class="bwdb_saison saison"><ul class="bwdb_saison saison">';

		$saison = bwdb_get_data( array( 'output' => 'saison', 'orderby' => 'ssn_id DESC' ) );
		foreach ( $saison as $saison ) {
			if ( ! isset( $attr['ssn_id'] ) ) {
				$attr['ssn_id'] = $saison->ssn_id;
			}
			$link = $_SERVER['REQUEST']; //reset
			$link = add_query_arg( 'ssn_id', $saison->ssn_id );

			if ( $attr['ssn_id'] == $saison->ssn_id ) {
				$class = 'class="active"';
			} else {
				$class = '';
			}

			$asktn_klss_ssn = sprintf( '<li %4$s><a href="%1$s" title="%2$s">%3$s</a></li>',
				$link,
				$saison->saison,
				$saison->saison,
				$class );

			echo $asktn_klss_ssn;
		}
		echo '</ul></div>';*/


	if ( 'best_off_hspl' == $attr['output'] || 'best_off_hser' == $attr['output'] ) {
		// do stuff

	} else {
	    global $wp;
        $current_url = home_url( add_query_arg( array(), $wp->request ) );
        $current_url = get_permalink();
		// Anzeige aller Städte
		$vereine = bwdb_get_data( array( 'output' => 'verein' ) );
		$verein  = "";

		foreach ( $vereine as $vrn ) {
			$link   = $current_url; //reset
			$link   = add_query_arg( array(
				'show'   => 'verein',
				'vrn_id' => $vrn->vrn_id,
				'ssn_id' => $attr['ssn_id']
			), $link );
			$verein .= sprintf( '<li class="menu-item"><a href="%1$s" title="%3$s">%3$s</a></li>',
				$link,
				$vrn->vrn_id,
				$vrn->verein
			);
		}
		echo "<nav id='bwdb-menu-verein'><ul class='nav-menu'>$verein</ul></nav>";

		?>
        <nav id="bwdb-menu">
            <ul class="nav-menu">
                <li class="menu-item">
                    <a href="<?php echo add_query_arg( array(
						'show'    => 'klss_ssn',
						'klss_id' => '',
						'ssn_id'  => $attr['ssn_id']
					), $current_url ); ?>">Results</a>
                </li>
                <li class="menu-item">
                    <a href="<?php echo add_query_arg( array(
						'show'   => 'schnitt',
						'sex'    => '0',
						'ssn_id' => $attr['ssn_id']
					), $current_url ); ?>">Women</a>
                </li class="menu-item">
                <li>
                    <a href="<?php echo add_query_arg( array(
						'show'   => 'schnitt',
						'sex'    => '1',
						'ssn_id' => $attr['ssn_id']
					), $current_url ); ?>">Men</a>
                </li class="menu-item">
            </ul>
        </nav>
		<?php
	}
	$show = $attr['show'];

// $debug = true;
	$debug = false;
	if ( true == $debug ) {
		echo '<h2> Aufruf erfolgt mit: </h2>';
		print_r( $attr );
		echo '<hr>';
	}

	switch ( $show ) {
		case "verein":
			bwdbShowVerein( $attr );
			break;
		case "sktn_klss_ssn":
			bwdbShowSektion( $attr );
			break;
		case "spieler":
			bwdbShowSpieler( $attr );
			break;
		case "klss_ssn";
			bwdbShowSktnList( $attr );
			break;
		case "vrn_ssn";
			bwdbShowVrnList( $attr );
			break;
		case "best_off";
			bwdbShowBest( $attr );
			break;
		case "schnitt":
			// nur für Betriebsliga - Sonderheit ... @todo eleganter lösen !!!!
			if ( $attr['ssn_id'] < 4 ) {
				$attr['bwrb_id'] = '1'; // bis Saison 2014/15 für Schnittliste nur TN aus 4er
			} else {
				// $attr['bwrb_id'] = '1,2,3'; // ab Saison 2015/16 für Schnittliste  TN aus 2er,4er
			}
			if ( empty( $attr['title'] ) ) {
				switch ( $_REQUEST['sex'] ) {
					case '0':
						$attr['title'] = "Results Women";
						break;
					case '1':
						$attr['title'] = "Results Men";
						break;
					default:
						$attr['title'] = "Results";
						break;
				}
			}
			bwdbShowAvgList( $attr );
			break;
		case "allevent":
			// nur für Betriebsliga - Sonderheit ... @todo eleganter lösen !!!!
			if ( $attr['ssn_id'] < 4 ) {
				$attr['bwrb_id'] = '1'; // bis Saison 2014/15 für Schnittliste nur TN aus 4er
			} else {
				$attr['bwrb_id'] = '1,2,3'; // ab Saison 2015/16 für Schnittliste  TN aus 2er,4er
			}
			if ( empty( $attr['title'] ) ) {
				switch ( $_REQUEST['sex'] ) {
					case '0':
						$attr['title'] = "All-Event Damen";
						break;
					case '1':
						$attr['title'] = "All-Event Herren";
						break;
					default:
						$attr['title'] = "Schnittliste";
						break;
				}
			}
			bwdbShowAvgList( $attr );
			break;
		default:
			bwdbShowAvgList( $attr );
			break;
	}

}


/*********************************************/
/* 			Funktion SCHNITT    			 */
/*********************************************/
function bwdbShowAvgList( $attr ) {

	// Aufruf Funktion Statistik

	$attr['single'] = true;

	if ( empty( $attr['orderby'] ) ) {
		$attr['orderby'] = 'schnitt DESC';
	}

	$schnittliste = bwdb_get_data( $attr );

	?>

	<?php if ( ! empty( $attr['title'] ) ) { ?>
        <h3><?php echo $attr['title']; ?></h3>
	<?php } ?>
    <table id="<?php echo $attr['id']; ?>" "class="bwdb" >

    <thead>
    <tr>
        <th></th>
        <th>Name</th>
        <th>Team</th>
        <th>Pins</th>
        <th>Sp.</th>
        <th>Avg</th>
        <th>HGm</th>
        <th>HSER</th>
        <th>%-Diff *</th>
        <th>NSp.</th>
        <th>%-Diff *</th>
        <th>DSp. **</th>
    </tr>
    </thead>
    <tfoot>
    </tfoot>
    </tbody>

	<?php
	$allevent = $attr['min'];
	$k        = 0;
	$current_url = get_permalink();


	foreach ( $schnittliste as $schnitt ) {            // Schleife Ausgabe Schnittliste
		if ( $schnitt->anz_allevent >= $allevent ) {    // Filter, wie viele Spiele notwendig sind, um in der Schnittliste aufzuscheinen.  @todo -> gehört in die Abfrage !!!!!!
			$k ++;
			?>
            <tr>
                <td align="right"><?php echo $k, '.'; ?></td>
                <td align="left">    <?php
					$link     = $current_url; //reset
					$link     = add_query_arg( array(
						'show'    => 'spieler',
						'splr_id' => $schnitt->splr_id,
						'ssn_id'  => $attr['ssn_id']
					), $link );
					$aspieler = sprintf( '<a href="%1$s" title="%5$s">%3$s %4$s</a>',
						$link,
						$schnitt->splr_id,
						$schnitt->vorname,
						$schnitt->nachname,
						$schnitt->sktn_list );
					echo $aspieler; ?></td>
                <td align="left"><?php
					$link    = $current_url; //reset
					$link    = add_query_arg( array(
						'show'   => 'verein',
						'vrn_id' => $schnitt->vrn_id,
						'ssn_id' => $attr['ssn_id']
					), $link );
					$averein = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
						$link,
						$schnitt->vrn_id,
						$schnitt->verein
					);
					echo $averein; ?></td>
                <td align="right"><?php echo $schnitt->pins; ?></td>
                <td align="right"><?php echo $schnitt->anzahl; ?></td>
                <td align="right"><?php echo $schnitt->schnitt; ?></td>
                <td align="right"><?php echo $schnitt->hsp; ?></td>
                <td align="right"><?php echo $schnitt->hser; ?></td>
                <td align="right"><?php echo $schnitt->avgmaxspl; ?>%</td>
                <td align="right"><?php echo $schnitt->minspl; ?></td>
                <td align="right"><?php echo $schnitt->avgminspl; ?>%</td>
                <td align="right"><?php echo $schnitt->diffspl; ?></td>
            </tr>
			<?php
		}
	}
	?>
    </table>

	<?php
}

function bwdbShowBest( $attr ) {

	// Aufruf Funktion Statistik

	$attr['limit'] = '5';

	echo '<h2>HSP Mannschaft</h2>';
	$attr['orderby'] = 'hsp DESC';
	bwdbShowSktnList( $attr );

	echo '<h2>HSER Mannschaft</h2>';
	$attr['orderby'] = 'hser DESC';
	bwdbShowSktnList( $attr );

	echo '<h2>HSP Damen</h2>';
	$attr['sex']     = '0';
	$attr['orderby'] = 'hsp DESC';
	bwdbShowAvgList( $attr );

	echo '<h2>HSER Damen</h2>';
	$attr['sex']     = '0';
	$attr['orderby'] = 'hser DESC';
	bwdbShowAvgList( $attr );

	echo '<h2>HSP Herren</h2>';
	$attr['sex']     = '1';
	$attr['orderby'] = 'hsp DESC';
	bwdbShowAvgList( $attr );

	echo '<h2>HSER Herren</h2>';
	$attr['sex']     = '1';
	$attr['orderby'] = 'hser DESC';
	bwdbShowAvgList( $attr );


}


/*********************************************/
/* 		Funktion SEKTIONS LISTE ANZEIGE		 */
/*********************************************/
function bwdbShowSktnList( $attr ) {

	global $wpdb;

	// Aufruf Funktion Statistik

	if ( empty( $attr['orderby'] ) ) {
		$attr['orderby'] = 'pins DESC';
	}

	$attr['team']    = 'true';
	$attr['reserve'] = '0';

	$data          = bwdb_get_data( $attr );
	$klassen_liste = "";
	//	$debug = true;
	$debug = false;
	if ( true == $debug ) {
		echo '<h2> Aufruf erfolgt mit: </h2>';
		print_r( $attr );
		echo '<hr>';
		echo $wpdb->last_query;
		echo '<hr>';
	}


	if ( ! empty( $_REQUEST['klss_id'] ) ) {
		$klss_id = $_REQUEST['klss_id'];
		$klasse  = bwdb_get_data( array( 'output' => 'klasse', 'klss_id' => $klss_id ) );

		foreach ( $klasse as $klss ) {
			$klassen_liste .= '| ' . $klss->klasse . ' |';
		}
	} else {
		$klassen_liste = "";
	}

	if ( empty( $attr['title'] ) ) {
		$attr['title'] = 'Team Statistics';
	}

	?>
    <div class="<?php echo $attr['id']; ?> data_table">

        <h3> <?php echo $attr['title']; ?>: <?php echo $klassen_liste; ?></h3>

        <table id="<?php echo $attr['id']; ?>" class="bwdb">
            <thead>
            <tr>
                <th></th>
                <th>Team</th>
                <th>Country</th>
                <th>Pins</th>
                <th>Games</th>
                <th>HGm.</th>
                <th>HSer.</th>
                <th>Avg.</th>
            </tr>
            </thead>
            </tbody>

			<?php

			$k = 0;
			$current_url = get_permalink();


			foreach ( $data as $team ) {
				$k ++;    // Schleife Ausgabe Team
				?>
                <tr>
                    <td align="right"><?php echo $k; ?></td>
                    <td align="left">    <?php
						$link           = $current_url; //reset
						$link           = add_query_arg( array(
							'show'    => 'sktn_klss_ssn',
							'sktn_id' => $team->sktn_id,
							'ssn_id'  => $attr['ssn_id']
						), $link );
						$asktn_klss_ssn = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
							$link,
							$team->sktn_klss_ssn_id,
							$team->sektion );
						echo $asktn_klss_ssn; ?></td>
                    <td align="left">    <?php
						$link   = $current_url; //reset
						$link   = add_query_arg( array(
							'show'   => 'verein',
							'vrn_id' => $team->vrn_id,
							'ssn_id' => $attr['ssn_id']
						), $link );
						$verein = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
							$link,
							$team->vrn_id,
							$team->verein );
						echo $verein; ?></td>
                    <td align="right"><?php echo $team->pins ?></td>
                    <td align="right"><?php echo $team->anzahl ?></td>
                    <td align="right"><?php echo $team->hsp ?></td>
                    <td align="right"><?php echo $team->hser ?></td>
                    <td align="right"><?php echo $team->schnitt ?></td>
                </tr>
			<?php } ?>
            </tbody>
        </table>
    </div>
	<?php
}/*********************************************/
/* 		Funktion VEREIN LISTE ANZEIGE		 */
/*********************************************/
function bwdbShowVrnList( $attr ) {

	global $wpdb;

	// Aufruf Funktion Statistik

	$attr['output']  = 'vrn_ssn';
	$attr['team']    = 'true';
	$attr['orderby'] = 'pins DESC';
	$attr['reserve'] = '0';

	$klassen_liste = "";

	$data = bwdb_get_data( $attr );
	//	$debug = true;
	$debug = false;
	if ( true == $debug ) {
		echo '<h2> Aufruf erfolgt mit: </h2>';
		print_bwdb( $attr, 'Attr' );
		echo '<hr>';
		echo $wpdb->last_query;
		echo '<hr>';
	}

	if ( ! empty( $_REQUEST['klss_id'] ) ) {
		$klss_id = $_REQUEST['klss_id'];
		$klasse  = bwdb_get_data( array( 'output' => 'klasse', 'klss_id' => $klss_id ) );

		foreach ( $klasse as $klss ) {
			$klassen_liste .= '| ' . $klss->klasse . ' |';
		}
	} else {
		$klassen_liste = "";
	}

	echo "<h3>Team Statisitics: $klassen_liste</h3>";

	?>
    <table id=<?php echo $attr['id']; ?> class="bwdb">
        <thead>
        <tr>
            <th></th>
            <th>Country</th>
            <th>Pins</th>
            <th>Games</th>
            <th>HGm.</th>
            <th>HSer.</th>
            <th>Avg.</th>
        </tr>
        </thead>
        </tbody>

		<?php
		$k = 0;
		$current_url = get_permalink();

		foreach ( $data as $team ) {
			$k ++;    // Schleife Ausgabe Team
			?>
            <tr>
                <td align="right"><?php echo $k; ?></td>
                <td align="left">    <?php
					$link   = $current_url; //reset
					$link   = add_query_arg( array(
						'show'   => 'verein',
						'vrn_id' => $team->vrn_id,
						'ssn_id' => $attr['ssn_id']
					), $link );
					$verein = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
						$link,
						$team->vrn_id,
						$team->verein );
					echo $verein; ?></td>
                <td align="right"><?php echo $team->pins ?></td>
                <td align="right"><?php echo $team->anzahl ?></td>
                <td align="right"><?php echo $team->hsp ?></td>
                <td align="right"><?php echo $team->hser ?></td>
                <td align="right"><?php echo $team->schnitt ?></td>
            </tr>
		<?php } ?>
        </tbody>
    </table>

	<?php
}


/*********************************************/
/* 			Funktion SPIELER    			 */
/*********************************************/
function bwdbShowSpieler( $attr ) {
	extract( $attr );
	$data = bwdb_get_data( $attr );


	// echo '<h2>Spieler: ' . $data[0]->vorname . ' ' . $data[0]->nachname . ' (Passnummer: ' . $data[0]->pnr . ')</h2>';
	echo '<h2>Player: ' . $data[0]->vorname . ' ' . $data[0]->nachname . '</h2>';

	bwdbShowAvgList( $attr );

	foreach ( $data AS $value ) {
		$result[ $value->sktn_klss_ssn_id ]['klasse']                                  = $value->klasse;
		$result[ $value->sktn_klss_ssn_id ]['anz_runden']                              = $value->anz_runden;
		$result[ $value->sktn_klss_ssn_id ]['data'][ $value->runde ][ $value->nummer ] = $value->ergebnis;

	}

	$spalten = $value->anz_spiele; // @todo
	?>

    <table id=<?php echo $attr['id']; ?> "class=" bwdb" >
    <thead>
    <tr>
        <th colspan="<?php echo $spalten + 4; ?>">Details</th>
    </tr>
    <tr>
        <th>Bewerb</th>
        <th>Rd</th>
		<?php for ( $x = 1;
		$x <= $spalten;
		++ $x ) { ?>
        <th>Spiel <?php echo $x;
			} ?></th>
        <th>Summe</th>
        <th>Tages-<br/>schnitt</th>
    </tr>
    </thead>
    <tbody>
	<?php

	foreach ( $result as $sktn_klss_ssn_id => $sktn_klss_ssn ) {

		for ( $i = 1; $i <= $sktn_klss_ssn['anz_runden']; ++ $i ) {

			if ( $sktn_klss_ssn['data'][ $i ] != 0 ) {
				?>
                <tr>
                <td align="left"><?php echo $sktn_klss_ssn['klasse']; ?></td>
                <td align="right"><?php echo $i; ?>:</td>
				<?php
				$summe = 0;
				$anz   = 0;
				for ( $n = 1; $n <= $spalten; ++ $n ) {
					$ergebnis = $sktn_klss_ssn['data'][ $i ][ $n ];
					$summe    += $ergebnis;
					?>
                    <td align="right"><?php if ( $ergebnis != 0 ) {
							echo $ergebnis;
							++ $anz;
						} else {
							echo "---";
						} ?></td>
					<?php
				}
				?>
                <td align="right"><?php echo $summe; ?></td>
                <td align="right"><?php echo sprintf( "%3.3f", round( $summe / $anz, 3 ) ); ?></td>
				<?php
			}
			?>
            </tr>
			<?php
		}
	} ?>
    </tbody>
    </table>
	<?php
}


/*********************************************/
/* 			Funktion VEREIN     			 */
/*********************************************/
/*
erwartet vrn_id, klss_id und ssn_id (filter)-> Anzeige der passenden Daten der Saison über sktn_klss_ssn

*/
function bwdbShowVerein( $attr ) {
	// $attr['min'] = 1;

	$sktn_vrn = bwdb_get_data( array(
		'output' => 'sktn_klss_ssn',
		'vrn_id' => $attr['vrn_id'],
		'ssn_id' => $attr['ssn_id']
	) );

	echo '<h2>Country: ' . $sktn_vrn[0]->verein . '</h2>';

	bwdbShowAvgList( $attr );

	// $attr['min'] = '0'; // ToDo: Implement!
	bwdbShowSktnList( $attr );
	?>

    <!--    <table>
        <thead>
        <tr>
            <th>Sektionsname:</th>
            <th>Spielklasse:</th>
        </tr>
        </thead>
        <tbody>

		<?php
	/*
			foreach ( $sktn_vrn AS $sktn_vrn ) {
				*/ ?>
            <tr>
                <td align="left">    <?php
	/*					$link           = $_SERVER['REQUEST']; //reset
						$link           = add_query_arg( array(
							'show'    => 'sktn_klss_ssn',
							'sktn_id' => $sktn_vrn->sktn_id,
							'ssn_id'  => $attr['ssn_id']
						), $link );
						$asktn_klss_ssn = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
							$link,
							$sktn_vrn->sktn_klss_ssn_id,
							$sktn_vrn->sektion );
						echo $asktn_klss_ssn; */ ?></td>
                <td align="left"><?php /*echo $sktn_vrn->klasse; */ ?></td>
            </tr>
			<?php
	/*		}
			*/ ?>
        </tbody>
    </table>-->

	<?php
}

/*********************************************/
/* 			Funktion SEKTION    			 */
/*********************************************/
function bwdbShowSektion( $attr ) {
	$attr['reserve'] = '';
	$attr['team']    = '';
	$attr['orderby'] = 'runde ASC';

	extract( $attr );
	$data = bwdb_get_data( $attr );

	echo '<h2>Team: ' . $data[0]->sektion . '</h2>';

	bwdbShowAvgList( $attr );

	foreach ( $data AS $value ) {
		$result[ $value->runde ]['date'] = $value->date;
		if ( 1 == $value->reserve ) {
			$result_res[ $value->runde ]['spieler'][ $value->splr_id ]['name']           = $value->vorname . ' ' . $value->nachname;
			$result_res[ $value->runde ]['spieler'][ $value->splr_id ][ $value->nummer ] = $value->ergebnis;
		} else {
			$result[ $value->runde ]['spieler'][ $value->splr_id ]['name']           = $value->vorname . ' ' . $value->nachname;
			$result[ $value->runde ]['spieler'][ $value->splr_id ][ $value->nummer ] = $value->ergebnis;
		}
	}

	?>

    <h2>Details</h2>

	<?php
	foreach ( $result as $nr => $runde ) {
		?>
        <table id=<?php echo $attr['id']; ?> class="bwdb" >
        <tbody>
        <tr>
            <td>Runde: <?php echo $nr; ?></td>
            <td colspan="<?php echo $data[0]->anz_spiele + 2; ?>">Datum: <?php echo $runde['date']; ?></td>
        </tr>
        <tr>
            <th>Player</th>
			<?php for ( $x = 1;
			$x <= $data[0]->anz_spiele;
			++ $x ) { ?>
            <th>Spiel <?php echo $x;
				} ?></th>
            <th>Summe</th>
            <th>Tages-<br/>schnitt</th>
        </tr>
		<?php
		$summe_gesamt           = 0;
		$spiele                 = 0;
		$summe_spiel_ergebnisse = array();
		foreach ( $runde[ spieler ] as $splr_id => $spieler ) {
			?>
            <tr>
                <td><?php echo $spieler[ name ]; ?></td>
				<?php
				$anz                      = 0;
				$summe_spieler_ergebnisse = 0;
				for ( $n = 1; $n <= $data[0]->anz_spiele; ++ $n ) {
					$ergebnis                     = $spieler[ $n ];
					$summe_spieler_ergebnisse     += $ergebnis; //aufsummieren der Spiele pro Spieler
					$summe_spiel_ergebnisse[ $n ] += $ergebnis; //aufsummieren der Spiele pro Spielrunde
					?>
                    <td align="right"><?php if ( $ergebnis != 0 ) {
							echo $ergebnis;
							++ $spiele;
							++ $anz;
						} else {
							echo "---";
						} ?></td>
					<?php
				}
				$summe_gesamt += $summe_spieler_ergebnisse; // Gesamtsumme ermitteln
				?>
                <td align="right"><?php echo $summe_spieler_ergebnisse; ?></td>
                <td align="right"><?php echo sprintf( "%3.3f", round( $summe_spieler_ergebnisse / $anz, 3 ) ); ?></td>
            </tr>
			<?php
		}
		?>
        <tr>
            <td>Result:</td>
			<?php foreach ( $summe_spiel_ergebnisse as $summe_spiel_ergebnis ) {
				echo '<td align="right">' . $summe_spiel_ergebnis . '</td>';
			} ?>
            <td align="right"><?php echo $summe_gesamt; ?></td>
            <td align="right"><?php echo sprintf( "%3.3f", round( $summe_gesamt / $spiele, 3 ) ); ?></td>
        </tr>

		<?php if ( ! empty( $result_res[ $nr ] ) ) {
			?>
            <tr>
                <td>Reserve:</td>
            </tr>
			<?php
			foreach ( $result_res[ $nr ][ spieler ] as $splr_id => $spieler ) {
				?>
                <tr>
                    <td><?php echo $spieler[ name ]; ?></td>
					<?php
					$summe = 0;
					$anz   = 0;
					for ( $n = 1; $n <= $data[0]->anz_spiele; ++ $n ) {
						$ergebnis = $spieler[ $n ];
						$summe    += $ergebnis;
						?>
                        <td align="right"><?php if ( $ergebnis != 0 ) {
								echo $ergebnis;
								++ $anz;
							} else {
								echo "---";
							} ?></td>
						<?php
					}
					?>
                    <td align="right"><?php echo $summe; ?></td>
                    <td align="right"><?php echo sprintf( "%3.3f", round( $summe / $anz, 3 ) ); ?></td>
                </tr>
				<?php
			}
		}


	} ?>
    </tbody>
    </table>

	<?php
}


/*********************************************/
/* 			Funktion STATISTIKEN			 */
/*********************************************/
function bwdb_get_data( $attr ) {

	// wp_die( '<pre>' . $attr  . '</pre>' );
	//NB Always set wpdb globally!
	global $wpdb;

	// Attribute für Shortcodes festlegen
//	$klss_ssn_id = $attr[klss_ssn];
//	$sktn_klss_ssn_id = $attr[sktn_klss_ssn];
//	$vrn_id = $attr[verein];
//	$spl_id = $attr[spieler];
//	$sex = $attr[sex];
//	$reserve = $attr[reserve];
//	$allevent = $attr[min];
//	$order = $attr[order];
//	$orderby = $attr[orderby];
//	$groupby = $attr[groupby];

	// @todo - Standardwerte setzten oder überprüfen bevor ins array eingetragen wird ...
	$sex     = '';
	$reserve = '';
	$output  = '';
	$limit   = '';
	$handicap = '10';

	extract( $attr );

	// Überprüfen ...

	if ( ! isset( $attr['nopaging'] ) ) {
		$attr['nopaging'] = 'true';
	}


	/******************************************************************************
	 * SHORTCODES
	 * klss_ssn        Klassen-id-saison (Bewerb)
	 * sktn_klss_ssn    Sektions-id-saison
	 * saison            Saison-id
	 * verein            Vereins-id
	 * spieler            Spieler-id (Passnummer)
	 * sex                Geschlecht (1 = m, 0 = w, nicht definiert gibt beides aus)
	 * reserve            Reserve    (1 = ja, 0 = nein, nicht definiert gibt beides aus)
	 * min                Mindestanzahl für Spiele, für All-Event-Listen z.B.
     * handicap
	 ********************************************************************************
	 * INTERNE ARRAY PARAMETER
	 * output            was wird gesucht Verein, Sektion, etc ... veränderung der fields&joins
	 * s                Suchparameter
	 * exact            Suchparameter (wenn gesetzt = exact )
	 * m                Jahr, Monat (z.B. 201209 für September 2012)
	 * nopaging        default = true, kein Pagination
	 * offset            wieviele Beiträge pro Seite angezeigt werden
	 * posts_per_page    wieviele Beiträge pro Seite angezeigt werden
	 * paged            Seite Nr. x
	 * no_found_rows    keine Datensätze gefunden
	 * single            Aufruf Schnittliste
	 * order
	 * orderby
	 * spezialabfrage    Abfragenspezifikum, die nicht überall gebraucht wird
	 * team             - " -
	 *******************************************************************************/


// FÜR WENN WAS ALS ARRAY KOMMT ^^
//    if ( is_array($pids) ) {
//       $id_list = "'" . implode("', '", $pids) . "'";


	// WHERE Abfrage basteln 1=1 damit man sich über AND keine Gedanken machen muß ;)
	// @todo is_numeric

	$where = '';

	if ( ! empty( $bwrb_id ) ) {
		$where .= " AND rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.rel_bwrb.ID IN ($bwrb_id)";
	}
	if ( ! empty( $klss_id ) ) {
		$where .= " AND rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.ID IN ($klss_id)";
	}
	if ( ! empty( $klss_ssn_id ) ) {
		$where .= " AND rel_sktn_klss_ssn.rel_klss_ssn.ID IN ($klss_ssn_id)";
	}
	if ( ! empty( $sktn_id ) ) {
		$where .= " AND rel_sktn_klss_ssn.rel_sktn.ID IN ($sktn_id)";
	}
	if ( ! empty( $sktn_klss_ssn_id ) ) {
		$where .= " AND rel_sktn_klss_ssn.ID IN ($sktn_klss_ssn_id)";
	}
	if ( ! empty( $ssn_id ) ) {
		$where .= " AND rel_sktn_klss_ssn.rel_klss_ssn.rel_ssn.ID IN ($ssn_id)";
	}
	if ( ! empty( $vrn_id ) ) {
		$where .= " AND rel_splr.rel_vrn.ID IN ($vrn_id)";
	}
	if ( ! empty( $splr_id ) ) {
		$where .= " AND rel_splr.ID IN ($splr_id)";
	}
	if ( ! empty( $spl_id ) ) {
		$where .= " AND t.ID IN ($spl_id)";
	}
	if ( ! empty( $min ) ) {
		// @TODO - derzeit wird nur nach 0 oder mehr als 0 unterschieden ;)
		// $where .= " AND t.ID IS NOT NULL";
	}

	if ( is_numeric( $sex ) ) {
		$where .= "  AND rel_splr.geschlecht.meta_value = ($sex)";
	}
	if ( is_numeric( $reserve ) ) {
		$where .= "  AND reserve = $reserve";
	}

	if ( ! is_numeric( $handicap ) ) {
		$handicap = '0';
	}


	// If a search pattern is specified, load the posts that match
	$search = '';
	if ( ! empty( $attr['s'] ) ) {
		// added slashes screw with quote grouping when done early, so done later
		$attr['s'] = stripslashes( $attr['s'] );
		if ( ! empty( $attr['sentence'] ) ) {
			$attr['search_terms'] = array( $attr['s'] );
		} else {
			preg_match_all( '/".*?("|$)|((?<=[\r\n\t ",+])|^)[^\r\n\t ",+]+/', $attr['s'], $matches );
			$attr['search_terms'] = array_map( '_search_terms_tidy', $matches[0] );
		}
		$n         = ! empty( $attr['exact'] ) ? '' : '%';
		$searchand = '';
		foreach ( (array) $attr['search_terms'] as $term ) {
			$term = esc_sql( like_escape( $term ) );
			// example $search .= "{$searchand}((s.sktn_klss_ssn_id LIKE '{$n}{$term}{$n}') OR (p.splr_id LIKE '{$n}{$term}{$n}'))";
			$search    .= "{$searchand}((p.nachname LIKE '{$n}{$term}{$n}') OR  (p.vorname LIKE '{$n}{$term}{$n}') OR (p.splr_id LIKE '{$n}{$term}{$n}'))";
			$searchand = ' AND ';
		}

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
			// @todo - amcht das sinn für uns ?
			if ( ! is_user_logged_in() ) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}
		$where .= $search;
	}

	// siehe query.php ^^ nach Jahr und Monat suchen
	if ( ! empty( $attr['m'] ) ) {
		$where .= " AND YEAR(rel_spl.date)=" . substr( $attr['m'], 0, 4 );
		$where .= " AND MONTH(rel_spl.date)=" . substr( $attr['m'], 4, 2 );
	}


	// Pagination
	// Paging wieder mal geklaut aus der query.php
	if ( empty( $attr['nopaging'] ) ) {
		$page = absint( $attr['paged'] );
		if ( ! $page ) {
			$page = 1;
		}

		if ( empty( $attr['offset'] ) ) {
			$pgstrt = ( $page - 1 ) * $attr['posts_per_page'] . ', ';
		} else { // we're ignoring $page and using 'offset'
			$attr['offset'] = absint( $attr['offset'] );
			$pgstrt         = $attr['offset'] . ', ';
		}
		$limits = 'LIMIT ' . $pgstrt . $attr['posts_per_page'];
	}

	$found_rows = '';
	if ( ! $attr['no_found_rows'] && ! empty( $limits ) ) {
		$found_rows = 'SQL_CALC_FOUND_ROWS';
	}


	// @todo maybe extract ergebnis to be only calculated if there is an handicap!
	$fields = "	    rel_splr.ID as splr_id,
					rel_splr.post_title as nachname,
					rel_splr.rel_vrn.post_title as verein,
					rel_splr.rel_vrn.ID as vrn_id,
					d.runde,
					d.datum as date,
					t.ID as spl_id,
					d.nummer as nummer,
					rel_sktn_klss_ssn.ID as sktn_klss_ssn_id,
					rel_sktn_klss_ssn.rel_sktn.post_title as sektion,
					rel_sktn_klss_ssn.rel_sktn.ID as sktn_id,
					rel_sktn_klss_ssn.rel_klss_ssn.ID as klss_ssn_id,
					rel_sktn_klss_ssn.rel_klss_ssn.rel_ssn.ID as ssn_id,
					rel_sktn_klss_ssn.rel_klss_ssn.spiele.meta_value as anz_spiele,
					rel_sktn_klss_ssn.rel_klss_ssn.runden.meta_value as anz_runden,
					rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.post_title as klasse,
					rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.rel_bwrb.ID as bwrb_id,
					rel_sktn_klss_ssn.rel_klss_ssn.rel_ssn.post_title as saison,
					d.reserve,
					rel_splr.geschlecht.meta_value as sex,
					d.ergebnis,
					";

	// für HANDICAP - CASE WHEN rel_splr.geschlecht.meta_value = '1' THEN d.ergebnis ELSE d.ergebnis+$handicap END as

	// Achtung Verein wird über Spieler ermittelt = aktueller Verein = fail fall sich der Verein ändert .. korriegieren Verein über sektion des Spiels ermitteln ...
	$pod_name = 'spl';


	// für Schnitt Berechnung
	if ( ! empty( $attr['single'] ) ) {

		$calculations = "	    SUM(ergebnis) AS pins,
								MAX(ergebnis) AS hsp,
								MIN(ergebnis) AS minspl,
								(MAX(ergebnis)-MIN(ergebnis)) AS diffspl,
								COUNT(ergebnis) AS anzahl,
								COUNT( CASE WHEN rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.rel_bwrb.ID IN (121775) THEN ergebnis END) AS anz_allevent,
								ROUND(AVG(ergebnis),3) AS schnitt,
								ROUND(((MAX(ergebnis)/AVG(ergebnis)*100)-100),2) AS avgmaxspl,
								ROUND(((MIN(ergebnis)/AVG(ergebnis)*100)-100),2) AS avgminspl,
								calc.hser AS hser,
								";

		$join = "   INNER JOIN (    SELECT h.splr_id, MAX(h.ser) as hser
                                    FROM (  SELECT `rel_splr`.`ID` as splr_id, SUM(d.ergebnis) AS ser
                                            FROM `bewp_posts` AS `t`
                                            LEFT JOIN `bewp_podsrel` AS `rel_rel_splr` ON `rel_rel_splr`.`field_id` = 121826 AND `rel_rel_splr`.`item_id` = `t`.`ID`
                                            LEFT JOIN `bewp_posts` AS `rel_splr` ON `rel_splr`.`ID` = `rel_rel_splr`.`related_item_id`
                                            LEFT JOIN `bewp_pods_spl` AS `d` ON `d`.`id` = `t`.`ID`
                                            WHERE ( ( `t`.`post_status` IN ( 'publish' ) ) AND ( `t`.`post_type` = 'spl' ) )
                                            GROUP BY splr_id , runde
                                            ORDER BY `t`.`menu_order`, `t`.`post_title`, `t`.`post_date`) AS h
                                    GROUP BY h.splr_id) AS calc ON calc.splr_id = rel_splr.ID ";

		$groupby = 'splr_id';
	}

	if ( ! empty( $attr['team'] ) ) {


		$sub_where = "reserve = 0";
		if ( ! empty( $runde ) ) {
			$sub_where .= " AND runde IN ($runde)";
		}

		$calculations = "		SUM(ergebnis) AS pins,
								COUNT(ergebnis) AS anzahl,
								ROUND(SUM(ergebnis)/COUNT(ergebnis),3) AS schnitt,
								MAX(calc.hspl) AS hsp,
								MAX(calc.serie) AS hser,
								";

		$join = '   INNER JOIN (    SELECT h.*, MAX(h.spl) as hspl, SUM(spl) as serie
                                    FROM (  SELECT `rel_sktn_klss_ssn`.`ID` as sktn_klss_ssn_id, runde, SUM(ergebnis) AS spl, nummer
                                            FROM `bewp_posts` AS `t`
                                            LEFT JOIN `bewp_podsrel` AS `rel_rel_sktn_klss_ssn` ON `rel_rel_sktn_klss_ssn`.`field_id` = 122016 AND `rel_rel_sktn_klss_ssn`.`item_id` = `t`.`ID` 
                                            LEFT JOIN `bewp_posts` AS `rel_sktn_klss_ssn` ON `rel_sktn_klss_ssn`.`ID` = `rel_rel_sktn_klss_ssn`.`related_item_id` 
                                            LEFT JOIN `bewp_pods_spl` AS `d` ON `d`.`id` = `t`.`ID` 
                                            WHERE ( ( ' . $sub_where . ' AND `t`.`post_status` IN ( "publish" ) ) AND ( `t`.`post_type` = "spl" ) )
                                            GROUP BY runde, nummer, sktn_klss_ssn_id
                                            ORDER BY `t`.`menu_order`, `t`.`post_title`, `t`.`post_date`) AS h
                                    GROUP BY h.sktn_klss_ssn_id, runde) AS calc ON calc.sktn_klss_ssn_id = `rel_sktn_klss_ssn`.`ID` AND calc.runde = d.runde';

		$groupby = 'sktn_klss_ssn_id';
	}


	// neue adaptierung - überschreibt wenn notwendig Dinge von vorher -> Ziel optimierung ...
	switch ( $output ) {

		case 'bewerb':
			$pod_name = 'FROM ' . $wpdb->$output . ' AS b';
			$fields   = '*, b.name as bewerb,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// $orderby = $output.'.name';
			break;

		case 'saison':
			$pod_name = 'ssn';
			$fields   = 't.post_title as saison, ID as ssn_id, beginndatum.meta_value as beginndatum, endedatum.meta_value as endedatum,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			$orderby  = '';

			break;

		case 'verein':
			$pod_name = 'vrn';
			$fields   = 't.*, t.post_title as verein, t.ID as vrn_id,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			$orderby  = 't.post_title';
			break;

		case 'vrn_ssn':
			$groupby = 'vrn_id';
			// $orderby  = '';
			// $where = '';
			break;

		case 'sektion':
			$pod_name = 'FROM ' . $wpdb->$output . ' AS s';
			$fields   = 't.*, s.name as sektion,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// $orderby = $output.'.name';
			break;

		case 'klasse':
			$pod_name = 'klss';
			$fields   = 't.ID as klss_id, post_title as klasse,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// maybe add bwrb_id
			// $orderby = $output.'.name';

			if ( ! empty( $klss_id ) ) {
				$where = "AND t.ID IN ($klss_id)";
			}
			// $where = '';
			break;

		case 'klss_ssn':
			$pod_name = 'FROM ' . $wpdb->$output . ' AS k
					LEFT JOIN ' . $wpdb->klasse . ' AS kn ON kn.klss_id = k.klss_id
					LEFT JOIN ' . $wpdb->saison . ' AS sa ON sa.ssn_id = k.ssn_id
					LEFT JOIN ' . $wpdb->bewerb . ' AS b ON b.bwrb_id = kn.bwrb_id';
			$fields   = '*, kn.name as klasse, 
			            sa.name as saison, 
			            b.name as bewerb,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// $orderby = $output.'.name';
			break;

		case 'sktn_klss_ssn':
			$pod_name = 'sktn_klss_ssn'; // zur ermittlung der Mitgliederanzahl
			$fields   = "    *,
			                rel_sktn.ID as sktn_id,
			                t.ID as sktn_klss_ssn_id, 
			                rel_sktn.post_title AS sektion,
			                rel_sktn.rel_vrn.post_title AS verein, 
			                rel_klss_ssn.rel_klss.post_title AS klasse, 
			                rel_klss_ssn.rel_ssn.post_title as saison, 
			                rel_klss_ssn.rel_klss.rel_bwrb.post_title as bewerb, 
			                0 AS anzahl,";
			$groupby  = "sktn_klss_ssn_id";
			$where    = '';
			if ( ! empty( $ssn_id ) ) {
				$where .= " AND rel_klss_ssn.rel_ssn.ID IN ($ssn_id)";
			}
			if ( ! empty( $vrn_id ) ) {
				$where .= " AND rel_sktn.rel_vrn.ID IN ($vrn_id)";
			}
			// $orderby = '';
			break;

		case 'spieler':
			$pod_name = 'splr';
			$fields   = "p.splr_id,
			            p.vrn_id,s.sktn_klss_ssn_id,k.klss_ssn_id,p.vorname,p.nachname,p.sex,v.name AS verein,sn.name AS sktn_klss_ssn, kn.name AS klss_ssn,";
			break;

		case 'best_off_hspl': // add off to activate ;)  MAX(IF(meta.meta_key = 'nickname', meta.meta_value, NULL)) AS 'nickname',

			$pod_name = 'splr';

			$fields = ' t.post_title as post_title,
                concat(t.post_title, " | ",rel_sktn_klss_ssn.rel_sktn.post_title) as nachname,
                t.ID as splr_id,
                geschlecht.meta_value as sex,
                rel_vrn.post_title as verein,
                rel_vrn.ID as vrn_id,
                rel_spl.d.runde,
                rel_spl.d.datum as date,
                rel_spl.ID as spl_id,
                rel_spl.d.nummer as nummer,
                rel_sktn_klss_ssn.ID as sktn_klss_ssn_id,
                rel_sktn_klss_ssn.rel_sktn.post_title as sektion,
                rel_sktn_klss_ssn.rel_sktn.ID as sktn_id,
                rel_sktn_klss_ssn.rel_klss_ssn.ID as klss_ssn_id,
                rel_sktn_klss_ssn.rel_klss_ssn.rel_ssn.ID as ssn_id,
                rel_sktn_klss_ssn.rel_klss_ssn.spiele.meta_value as anz_spiele,
                rel_sktn_klss_ssn.rel_klss_ssn.runden.meta_value as anz_runden,
                rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.post_title as klasse,
                rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.rel_bwrb.ID as bwrb_id,
                rel_sktn_klss_ssn.rel_klss_ssn.rel_ssn.post_title as saison,
                rel_spl.d.reserve,
                rel_spl.d.ergebnis,';


			if ( ! empty( $attr['single'] ) ) {

				$calculations = "0 AS pins,
                                ergebnis AS hsp,
                                0 AS minspl,
                                0 AS diffspl,
                                0 AS anzahl,
                                20 AS anz_allevent,
                                0 AS schnitt,
                                0 AS avgmaxspl,
                                0 AS avgminspl,
                                0 AS hser,
                                ";

				$groupby = '';
				$join    = '';
				if ( is_numeric( $sex ) ) {
					$where = "  AND geschlecht.meta_value = ($sex)";
				}

			}

			if ( ! empty( $attr['team'] ) ) {

				$calculations = "0 AS pins,
                                SUM(ergebnis) AS hsp,
                                0 AS minspl,
                                0 AS diffspl,
                                0 AS anzahl,
                                20 AS anz_allevent,
                                0 AS schnitt,
                                0 AS avgmaxspl,
                                0 AS avgminspl,
                                0 AS hser,
                                ";


				$groupby = 'sktn_klss_ssn_id, runde, nummer';

				$join = '';
			}


			break;

		case 'best_off_hser': // add off to activate ;)  MAX(IF(meta.meta_key = 'nickname', meta.meta_value, NULL)) AS 'nickname',
			$debug = false;

			$pod_name = 'splr';

			$fields = ' t.post_title as nachname,
                concat(t.post_title, " | ",rel_sktn_klss_ssn.rel_sktn.post_title) as nachname,
                t.ID as splr_id,
                geschlecht.meta_value as sex,
                rel_vrn.post_title as verein,
                rel_vrn.ID as vrn_id,
                rel_spl.d.runde,
                rel_spl.d.datum as date,
                rel_spl.ID as spl_id,
                rel_spl.d.nummer as nummer,
                rel_sktn_klss_ssn.ID as sktn_klss_ssn_id,
                rel_sktn_klss_ssn.rel_sktn.post_title as sektion,
                rel_sktn_klss_ssn.rel_sktn.ID as sktn_id,
                rel_sktn_klss_ssn.rel_klss_ssn.ID as klss_ssn_id,
                rel_sktn_klss_ssn.rel_klss_ssn.rel_ssn.ID as ssn_id,
                rel_sktn_klss_ssn.rel_klss_ssn.spiele.meta_value as anz_spiele,
                rel_sktn_klss_ssn.rel_klss_ssn.runden.meta_value as anz_runden,
                rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.post_title as klasse,
                rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.rel_bwrb.ID as bwrb_id,
                rel_sktn_klss_ssn.rel_klss_ssn.rel_ssn.post_title as saison,
                rel_spl.d.reserve,
                rel_spl.d.ergebnis,';

			if ( ! empty( $attr['single'] ) ) {

				$calculations = "0 AS pins,
                                0 AS hsp,
                                0 AS minspl,
                                0 AS diffspl,
                                0 AS anzahl,
                                20 AS anz_allevent,
                                0 AS schnitt,
                                0 AS avgmaxspl,
                                0 AS avgminspl,
                                SUM(ergebnis) AS hser,
                                ";

				$groupby = 'splr_id, runde';
				$join    = '';
				if ( is_numeric( $sex ) ) {
					$where = "  AND geschlecht.meta_value = ($sex)";
				}

			}

			if ( ! empty( $attr['team'] ) ) {

				$calculations = "0 AS pins,
                                0 AS hsp,
                                0 AS minspl,
                                0 AS diffspl,
                                0 AS anzahl,
                                20 AS anz_allevent,
                                0 AS schnitt,
                                0 AS avgmaxspl,
                                0 AS avgminspl,
                                SUM(ergebnis) AS hser,
                                ";


				$groupby = 'sktn_klss_ssn_id, runde';

				$join = '';
			}


			break;
		default:
	}

	// IDEAS Best off based on splr instead of spl
	/*				if ( ! empty( $attr['single'] ) ) {
					$pod_name = 'splr';

					$calculations = "0 AS pins,
									ergebnis AS hsp,
									0 AS minspl,
									0 AS diffspl,
									0 AS anzahl,
									20 AS anz_allevent,
									0 AS schnitt,
									0 AS avgmaxspl,
									0 AS avgminspl,
									0 AS hser,
									";

					$fields = ' t.post_title as nachname,
						geschlecht.meta_value as sex,nachname.meta_value as nachname,vorname.meta_value as vorname,
						rel_vrn.post_title as verein,
						rel_vrn.ID as vrn_id,
						rel_spl.d.runde,
						rel_spl.d.datum as date,
						rel_spl.ID as spl_id,
						rel_spl.d.nummer as nummer,
						rel_sktn_klss_ssn.ID as sktn_klss_ssn_id,
						rel_sktn_klss_ssn.rel_sktn.post_title as sektion,
						rel_sktn_klss_ssn.rel_sktn.ID as sktn_id,
						rel_sktn_klss_ssn.rel_klss_ssn.ID as klss_ssn_id,
						rel_sktn_klss_ssn.rel_klss_ssn.rel_ssn.ID as ssn_id,
						rel_sktn_klss_ssn.rel_klss_ssn.spiele.meta_value as anz_spiele,
						rel_sktn_klss_ssn.rel_klss_ssn.runden.meta_value as anz_runden,
						rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.post_title as klasse,
						rel_sktn_klss_ssn.rel_klss_ssn.rel_klss.rel_bwrb.ID as bwrb_id,
						rel_sktn_klss_ssn.rel_klss_ssn.rel_ssn.post_title as saison,
						rel_spl.d.reserve,
						rel_spl.d.ergebnis,

					';
					$groupby = '';
					$join = '';
					if ( is_numeric( $sex ) ) {
						$where = "  AND geschlecht.meta_value = ($sex)";
					}
					$orderby = 'ergebnis DESC';

				}*/


	// NEW - PODS

	// TEST - für Join von PODS zum Anpassen!!!
	/*	$test = false;
		if ( $test ) {
			$fields = "
						d.runde,
						t.ID as spl_id,
						ergebnis,
						rel_sktn_klss_ssn.ID as sktn_klss_ssn_id,
						";


			// Achtung Verein wird über Spieler ermittelt = aktueller Verein = fail fall sich der Verein ändert .. korriegieren Verein über sektion des Spiels ermitteln ...
			$pod_name = 'spl';


			// für Schnitt Berechnung
			if ( ! empty( $attr['single'] ) ) {

				$calculations = "	SUM(ergebnis) AS ser,

									";

				$join = "";

				$groupby = 'splr_id, runde';
			}

			if ( ! empty( $attr['team'] ) ) {
				$calculations = "	SUM(ergebnis) AS pins,
									COUNT(ergebnis) AS anzahl,
									ROUND(SUM(ergebnis)/COUNT(ergebnis),3) AS schnitt,
									0 AS hser,
									SUM(ergebnis) AS hsp,";
				$join = "";

				$groupby = 'sktn_klss_ssn_id, runde, nummer';
			}
			$where = '';
		}*/
	// Sortierung @todo
	if ( ! empty( $where ) ) {
		$where = ' 1=1 ' . $where;
	}

	if ( ! is_numeric( $limit ) ) {
		$limit = - 1;
	}


	$select = $found_rows . ' ' . $fields . ' ' . $calculations . ' 1+1';

	// limit -1 für alle ...
	$params = array(
		'limit'   => $limit,
		'select'  => $select,
		'where'   => $where,
		'groupby' => $groupby,
		'orderby' => $orderby,
		'join'    => $join,
		'expires' => '300',

	);

	$pods_object = pods( $pod_name, $params );

	/*	$pods_object->fetch();
		echo $pods_object->field( 'vorname');
		print_bwdb( $pods_object->field( 'meta_value') );*/

	if ( $pods_object ) {
		$result = $pods_object->data();
	}

	// @debug:
	// $debug = false;
	if ( empty( $debug ) ) {
		$debug = false;
	}

	if ( true == $debug && current_user_can( 'manage_options' ) ) {
		print_bwdb( $attr, 'Attr' );
		print_bwdb( $params, 'Params' );
		// print_bwdb( $pod_name, 'Pod' );

		$wpdb->print_error;
		print_bwdb( $wpdb->last_query, 'Query' );

		if ( $pods_object ) {
			print_bwdb( array_keys( $pods_object->fields() ), 'Fields' );
		}

		print_bwdb( $result, 'Object' );
		echo "<br /><hr />";
		// bwdb_get_data_old( $attr );
	}

	return $result;


	/******************************************************************************
	 * OBJEKT-AUSGABEN
	 * ->vorname        Vorname
	 * ->nachname        Nachname
	 * ->splr_id        Passnummer
	 * ->verein        Vereinsname
	 * ->pins            Gespielte Pins Insgesamt
	 * ->hsp        Höchstes Spiel
	 * ->minspl        Niedrigstes Spiel
	 * ->diffspl        Differenz zwischen höchstem und niedrigstem Spiel
	 * ->anzahl        Anzahl der gespielten Spiele
	 * ->schnitt        Schnitt (Gespielte Pins dividiert durch Anzahl der gespielten Spiele)
	 * ->avgmaxspl        Abweichung in Prozent: Höchstes Spiel vom Schnitt
	 * ->avgminspl        Abweichung in Prozent: Niedrigstes Spiel vom Schnitt
	 *******************************************************************************/

}

// @todo verbessern ;) - welche Tabele solls sein und welche spalte = auch im Request dieselbe id !!!!
// $where um die Abfrage zu filtern ....
function bwdb_dropdown( $output, $col, $attr = array() ) {
	global $wpdb;

	switch ( $output ) {

		case 'klss_ssn':
			$orderby = 'klasse';
			break;
		case 'sktn_klss_ssn':
			$orderby = 'sektion';
			break;
		case 'bewerb':
			$orderby = 'bewerb';
			break;

		default:
			$orderby = $output;
			break;

	}


	$attr['output']  = $output;
	$attr['orderby'] = $orderby;

	$result = bwdb_get_data( $attr );


	$id = isset( $_REQUEST[ $col ] ) ? (int) $_REQUEST[ $col ] : 0;

//		debug
//		echo $wpdb->last_query;
//		echo $wpdb->print_error;
//		print_r($result);
	?>

    <select name="<?php echo $col ?>" size="1">
        <option<?php selected( $id, '0' ); ?> value='0'><?php _e( 'Show all' ); ?></option>
		<?php
		foreach ( $result as $val ) {
			if ( 'sktn_klss_ssn' == $output ) { // wenn sktn_klss_ssn -> wird die Klasse angehängt (Übersicht)
				$name = esc_attr( $val->sektion ) . ' / ' . esc_attr( $val->klasse );
			} else {
				$name = esc_attr( $val->$orderby );
			}

			printf( "<option %s value='%s'>%s</option>\n",
				selected( $id, $val->$col, false ),
				esc_attr( $val->$col ),
				$name
			);
		}
		?>
    </select>
	<?php
}

// DEvelopment
function bwdb_get_sktn_klss_ssn( $attr ) {

	$data = bwdb_get_data( $attr );
}

function resave_pod_items() {
	// Create and find in one shot
	$your_cpt = pods( 'your_cpt' )->find();
	if ( 0 < $your_cpt->total() ) {
		while ( $your_cpt->fetch() ) {
			$your_cpt->save( 'your_relationship_field', $your_cpt->field( 'your_relationship_field' ) );
		} // end of your_cpt loop
	} // end of found your_cpt
}