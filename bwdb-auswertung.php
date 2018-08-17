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
// @todo:  $attr durchgängi nutzen nicht mla aus dem Request und mal so möglich?

	$base           = $_SERVER['REQUEST'];
	$attr['ssn_id'] = $_REQUEST['ssn_id'];

	// Ausgabe der Saisonen in einer ul - die aktuelle Saison erhält die klasse: active
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
	echo '</ul></div>';
	?>

	<div class="bwdb_result saison">
		<ul class="bwdb_schnitt saison">
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'   => 'schnitt',
					'sex'    => '0',
					'ssn_id' => $attr['ssn_id']
				), $base ); ?>">Damen</a>
			</li>
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'   => 'schnitt',
					'sex'    => '1',
					'ssn_id' => $attr['ssn_id']
				), $base ); ?>">Herren</a>
			</li>
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'   => 'allevent',
					'sex'    => '0',
					'min'    => '21',
					'ssn_id' => $attr['ssn_id']
				), $base ); ?>">All-Event Damen</a>
			</li>
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'   => 'allevent',
					'sex'    => '1',
					'min'    => '21',
					'ssn_id' => $attr['ssn_id']
				), $base ); ?>">All-Event Herren</a>
			</li>
		</ul>
		<ul class="bwdb_bewerbe saison">
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'    => 'klss_ssn',
					'klss_id' => '1,2,3',
					'ssn_id'  => $attr['ssn_id']
				), $base ); ?>">4er
					Gesamt</a></li>
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'    => 'klss_ssn',
					'klss_id' => '1',
					'ssn_id'  => $attr['ssn_id']
				), $base ); ?>">4er
					Gruppe A</a></li>
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'    => 'klss_ssn',
					'klss_id' => '2',
					'ssn_id'  => $attr['ssn_id']
				), $base ); ?>">4er
					Gruppe B</a></li>
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'    => 'klss_ssn',
					'klss_id' => '3',
					'ssn_id'  => $attr['ssn_id']
				), $base ); ?>">4er
					Gruppe C</a></li>
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'    => 'klss_ssn',
					'klss_id' => '4',
					'ssn_id'  => $attr['ssn_id']
				), $base ); ?>">Damen-Doppel</a>
			</li>
			<li>
				<a href="<?php echo add_query_arg( array(
					'show'    => 'klss_ssn',
					'klss_id' => '5',
					'ssn_id'  => $attr['ssn_id']
				), $base ); ?>">Mix-Doppel</a>
			</li>
		</ul>
	</div>
	<hr/>
	<?php

	$show = $attr['show'];

	// $debug = true;
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
		case "schnitt":
			// nur für Betriebsliga - Sonderheit ... @todo eleganter lösen !!!!
			if ( $attr['ssn_id'] < 4 ) {
				$attr['bwrb_id'] = '1'; // bis Saison 2014/15 für Schnittliste nur TN aus 4er
			} else {
				$attr['bwrb_id'] = '1,2,3'; // ab Saison 2015/16 für Schnittliste  TN aus 2er,4er
			}
			switch ( $_REQUEST['sex'] ) {
				case '0':
					echo '<h2>Schnittliste Damen</h2>';
					break;
				case '1':
					echo '<h2>Schnittliste Herren</h2>';
					break;
				default:
					echo '<h2>Schnittliste</h2>';
					break;
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
			switch ( $_REQUEST['sex'] ) {
				case '0':
					echo '<h2>All-Event Damen</h2>';
					break;
				case '1':
					echo '<h2>All-Event Herren</h2>';
					break;
				default:
					echo '<h2>Schnittliste</h2>';
					break;
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

	$attr['single']  = true;
	$attr['orderby'] = 'schnitt DESC';


	$schnittliste = bwdb_get_data( $attr );

	?>

	<table id="bwdb" "class="bwdb" >
		<thead>
		<tr>
			<th></th>
			<th>Pass#</th>
			<th>Spielername</th>
			<th>Verein</th>
			<th>Pins</th>
			<th>Sp.</th>
			<th>Schnitt</th>
			<th>HSp.</th>
			<th>%-Abw *</th>
			<th>NSp.</th>
			<th>%-Abw *</th>
			<th>DSp. **</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="12">* Prozentuelle Abweichung vom Schnitt</td>
		</tr>
		<tr>
			<td colspan="12">** Differenz zwischen Höchstem und Niedrigstem Spiel</td>
		</tr>
		</tfoot>
		</tbody>

		<?php
		$allevent = $attr['min'];
		$k        = 0;

		foreach ( $schnittliste as $schnitt ) {            // Schleife Ausgabe Schnittliste
			if ( $schnitt->anz_allevent >= $allevent ) {    // Filter, wie viele Spiele notwendig sind, um in der Schnittliste aufzuscheinen.  @todo -> gehört in die Abfrage !!!!!!
				$k ++;
				?>
				<tr>
					<td align="right"><?php echo $k, '.'; ?></td>
					<td align="right"><?php echo $schnitt->splr_id; ?></td>
					<td align="left">    <?php
						$link     = $_SERVER['REQUEST']; //reset
						$link     = add_query_arg( array(
							'show'    => 'spieler',
							'splr_id' => $schnitt->splr_id,
							'ssn_id'  => $attr['ssn_id']
						), $link );
						$aspieler = sprintf( '<a href="%1$s" title="%2$s">%3$s %4$s</a>',
							$link,
							$schnitt->splr_id,
							$schnitt->vorname,
							$schnitt->nachname );
						echo $aspieler; ?></td>
					<td align="left"><?php
						$link    = $_SERVER['REQUEST']; //reset
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
					<td align="right"><?php echo $schnitt->maxspl; ?></td>
					<td align="right"><?php echo $schnitt->avgmaxspl; ?>%</td>
					<td align="right"><?php echo $schnitt->minspl; ?></td>
					<td align="right"><?php echo $schnitt->avgminspl; ?>%</td>
					<td align="right"><?php echo $schnitt->diffspl; ?></td>
				</tr>
				<?php
			}
		}
		?>
		<tbody>
	</table>

	<?php
}


/*********************************************/
/* 		Funktion SEKTIONS LISTE ANZEIGE		 */
/*********************************************/
function bwdbShowSktnList( $attr ) {

	global $wpdb;

	// Aufruf Funktion Statistik

	$attr['team']    = 'true';
	$attr['orderby'] = 'pins DESC';
	$attr['reserve'] = '0';

	$data = bwdb_get_data( $attr );
	//	$debug = true;
	if ( true == $debug ) {
		echo '<h2> Aufruf erfolgt mit: </h2>';
		print_r( $attr );
		echo '<hr>';
		echo $wpdb->last_query;
		echo '<hr>';
	}


	$klasse = bwdb_get_data( array( 'output' => 'klasse', 'klss_id' => $_REQUEST['klss_id'] ) );

	?>


	<h2><?php foreach ( $klasse as $klasse ) {
			echo '/ ' . $klasse->klasse . ' /';
		} ?></h2>
	<table id="bwdb" class="bwdb" >
		<thead>
		<tr>
			<th></th>
			<th>Sektion</th>
			<th>Pins</th>
			<th>Spiele</th>
			<th>HSp.</th>
			<th>HSer.</th>
			<th>Schnitt</th>
		</tr>
		</thead>
		</tbody>

		<?php

		$k = 0;
		foreach ( $data as $team ) {
			$k ++;    // Schleife Ausgabe Team
			?>
			<tr>
				<td align="right"><?php echo $k; ?></td>
				<td align="left">    <?php
					$link           = $_SERVER['REQUEST']; //reset
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

	echo '<h2>Spieler: ' . $data[0]->vorname . ' ' . $data[0]->nachname . ' (Passnummer: ' . $data[0]->splr_id . ')</h2>';

	bwdbShowAvgList( $attr );

	foreach ( $data AS $value ) {
		$result[ $value->sktn_klss_ssn_id ][ klasse ]                                  = $value->klasse;
		$result[ $value->sktn_klss_ssn_id ][ anz_runden ]                              = $value->anz_runden;
		$result[ $value->sktn_klss_ssn_id ][ data ][ $value->runde ][ $value->nummer ] = $value->ergebnis;

	}

	$spalten = 6; // @todo
	?>

	<table id="bwdb" "class="bwdb" >
		<thead>
		<tr>
			<th colspan="<?php echo $spalten + 4; ?>">Ergebnis-Details</th>
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

			for ( $i = 1; $i <= $sktn_klss_ssn[ anz_runden ]; ++ $i ) {

				if ( $sktn_klss_ssn[ data ][ $i ] != 0 ) {
					?>
					<tr>
					<td align="left"><?php echo $sktn_klss_ssn[ klasse ]; ?></td>
					<td align="right"><?php echo $i; ?>:</td>
					<?php
					$summe = 0;
					$anz   = 0;
					for ( $n = 1; $n <= $spalten; ++ $n ) {
						$ergebnis = $sktn_klss_ssn[ data ][ $i ][ $n ];
						$summe += $ergebnis;
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
	$attr['min'] = 1;

	echo '<h2>Verein: ' . $sktn_vrn[0]->verein . '</h2>';

	bwdbShowAvgList( $attr );
	?>

	<table>
		<thead>
		<tr>
			<th>Sektionsname:</th>
			<th>Spielklasse:</th>
		</tr>
		</thead>
		<tbody>

		<?php
		$sktn_vrn = bwdb_get_data( array(
			'output' => 'sktn_klss_ssn',
			'vrn_id' => $attr[ vrn_id ],
			'ssn_id' => $attr[ ssn_id ]
		) );

		foreach ( $sktn_vrn AS $sktn_vrn ) {
			?>
			<tr>
				<td align="left">    <?php
					$link           = $_SERVER['REQUEST']; //reset
					$link           = add_query_arg( array(
						'show'    => 'sktn_klss_ssn',
						'sktn_id' => $sktn_vrn->sktn_id,
						'ssn_id'  => $attr['ssn_id']
					), $link );
					$asktn_klss_ssn = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
						$link,
						$sktn_vrn->sktn_klss_ssn_id,
						$sktn_vrn->sektion );
					echo $asktn_klss_ssn; ?></td>
				<td align="left"><?php echo $sktn_vrn->klasse; ?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>

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

	echo '<h2>Sektion: ' . $data[0]->sektion . '</h2>';

	bwdbShowAvgList( $attr );

	foreach ( $data AS $value ) {
		$result[ $value->runde ][ date ] = $value->date;
		if ( 1 == $value->reserve ) {
			$result_res[ $value->runde ][ spieler ][ $value->splr_id ][ name ]           = $value->vorname . ' ' . $value->nachname;
			$result_res[ $value->runde ][ spieler ][ $value->splr_id ][ $value->nummer ] = $value->ergebnis;
		} else {
			$result[ $value->runde ][ spieler ][ $value->splr_id ][ name ]           = $value->vorname . ' ' . $value->nachname;
			$result[ $value->runde ][ spieler ][ $value->splr_id ][ $value->nummer ] = $value->ergebnis;
		}
	}

	?>

	<h2> Ergebnis-Details</h2>

	<?php
	foreach ( $result as $nr => $runde ) {
		?>
		<table "class="bwdb" >
		<tbody>
		<tr>
			<td>Runde: <?php echo $nr; ?></td>
			<td colspan="<?php echo $data[0]->anz_spiele + 2; ?>">Datum: <?php echo $runde[ date ]; ?></td>
		</tr>
		<tr>
			<th>Spieler</th>
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
					$ergebnis = $spieler[ $n ];
					$summe_spieler_ergebnisse += $ergebnis; //aufsummieren der Spiele pro Spieler
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
			<td>Mannschaftsergebnis:</td>
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
						$summe += $ergebnis;
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

	// @todo - Standardwerte setzten oder überprüfen beovr ins array eingetragen wird ...
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

	if ( ! empty( $bwrb_id ) ) {
		$where .= " AND b.bwrb_id IN ($bwrb_id)";
	}
	if ( ! empty( $klss_id ) ) {
		$where .= " AND k.klss_id IN ($klss_id)";
	}
	if ( ! empty( $klss_ssn_id ) ) {
		$where .= " AND k.klss_ssn_id IN ($klss_ssn_id)";
	}
	if ( ! empty( $sktn_id ) ) {
		$where .= " AND s.sktn_id IN ($sktn_id)";
	}
	if ( ! empty( $sktn_klss_ssn_id ) ) {
		$where .= " AND s.sktn_klss_ssn_id IN ($sktn_klss_ssn_id)";
	}
	if ( ! empty( $ssn_id ) ) {
		$where .= " AND k.ssn_id IN ($ssn_id)";
	}
	if ( ! empty( $vrn_id ) ) {
		$where .= " AND v.vrn_id IN ($vrn_id)";
	}
	if ( ! empty( $splr_id ) ) {
		$where .= " AND p.splr_id IN ($splr_id)";
	}
	if ( ! empty( $spl_id ) ) {
		$where .= " AND spl_id IN ($spl_id)";
	}
	if ( ! empty( $min ) ) {
		// @TODO - derzeit wird nur nach 0 oder mehr als 0 unterschieden ;)
		$where .= " AND spl_id IS NOT NULL";
	}

	if ( is_numeric( $sex ) ) {
		$where .= "  AND sex = $sex";
	}
	if ( is_numeric( $reserve ) ) {
		$where .= "  AND reserve = $reserve";
	}


	// If a search pattern is specified, load the posts that match
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
			$search .= "{$searchand}((p.nachname LIKE '{$n}{$term}{$n}') OR  (p.vorname LIKE '{$n}{$term}{$n}') OR (p.splr_id LIKE '{$n}{$term}{$n}'))";
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
		$where .= " AND YEAR(g.date)=" . substr( $attr['m'], 0, 4 );
		$where .= " AND MONTH(g.date)=" . substr( $attr['m'], 4, 2 );
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


//  nicht mehr Notwendig -> aufheben ? 	
//	$lastkey = array_pop(array_keys($whereA));
//	foreach  ( $whereA as $key => $value ) {
//		$where .= $value;
//		if ($key != $lastkey) $where .= " AND ";
//	}


	// Daten auslesen .. $distinct , $fields …. noch anpassen//erstellen
	// @ToDo: !!!!


	$fields .= "	p.splr_id,
					p.vorname,
					p.nachname,
					v.name as verein,
					v.vrn_id,
					g.runde,
					g.date,
					g.spl_id,
					g.nummer,
					z.sktn_klss_ssn_id,
					sn.name as sektion,
					sn.sktn_id,
					k.klss_ssn_id,
					k.ssn_id,
					k.anz_spiele,
					k.anz_runden,
					kn.name as klasse,
					sa.name as saison,
					g.reserve,
					p.sex,
					g.ergebnis,
					";


	// Achtung Verein wird über Spieler ermittelt = aktueller Verein = fail fall sich der Verein ändert .. korriegieren Verein über sektion des Spiels ermitteln ...
	$join .= "	FROM $wpdb->spieler AS p
				LEFT JOIN $wpdb->splr_sktn_klss_ssn AS z ON z.splr_id = p.splr_id
				LEFT JOIN $wpdb->spiel AS g ON p.splr_id = g.splr_id AND z.sktn_klss_ssn_id = g.sktn_klss_ssn_id
				LEFT JOIN $wpdb->verein AS v ON v.vrn_id = p.vrn_id
				LEFT JOIN $wpdb->sktn_klss_ssn AS s ON s.sktn_klss_ssn_id = z.sktn_klss_ssn_id
				LEFT JOIN $wpdb->sektion AS sn ON sn.sktn_id = s.sktn_id
				LEFT JOIN $wpdb->klss_ssn AS k ON k.klss_ssn_id = s.klss_ssn_id
				LEFT JOIN $wpdb->klasse AS kn ON kn.klss_id = k.klss_id
				LEFT JOIN $wpdb->saison AS sa ON sa.ssn_id = k.ssn_id
				LEFT JOIN $wpdb->bewerb AS b ON b.bwrb_id = kn.bwrb_id";

	// für Schnitt Berechnung
	if ( ! empty( $attr['single'] ) ) {

		// todo Herausfidne warum die Abfrage in der DB geht über WP aber nicht ...
		$jointodo .= "
					INNER JOIN 
					(SELECT
							h.splr_id,
							MAX(h.hser) AS maxser

							FROM
									(SELECT   g.splr_id AS splr_id,
									SUM(g.ergebnis) AS hser
									FROM wp_bwdb_spiel AS g
									LEFT JOIN wp_bwdb_sktn_klss_ssn AS s ON s.sktn_klss_ssn_id = g.sktn_klss_ssn_id
									LEFT JOIN wp_bwdb_klss_ssn AS k ON k.klss_ssn_id = s.klss_ssn_id
									LEFT JOIN wp_bwdb_saison AS sa ON k.ssn_id = sa.ssn_id
									LEFT JOIN wp_bwdb_klasse AS kn ON kn.klss_id = k.klss_id
									WHERE sa.ssn_id = 1 AND kn.bwrb_id = 1
									GROUP BY k.klss_id, g.runde, g.splr_id) AS h

							GROUP BY h.splr_id
					  ) AS tbl_maxser ON tbl_maxser.splr_id = p.splr_id";

		// tbl_maxser.maxser AS hser,  sobald oben funktioniert ;)
		$calculations .= "	SUM(g.ergebnis) AS pins,
								MAX(g.ergebnis) AS maxspl,
								MIN(g.ergebnis) AS minspl,
								(MAX(g.ergebnis)-MIN(g.ergebnis)) AS diffspl,
								COUNT(g.ergebnis) AS anzahl,
								COUNT( CASE WHEN  b.bwrb_id IN (1) THEN g.ergebnis END) AS anz_allevent,
								ROUND(AVG(g.ergebnis),3) AS schnitt,
								ROUND(((MAX(g.ergebnis)/AVG(g.ergebnis)*100)-100),2) AS avgmaxspl,
								ROUND(((MIN(g.ergebnis)/AVG(g.ergebnis)*100)-100),2) AS avgminspl,
								";

		$groupby = 'p.splr_id';
	}

	if ( ! empty( $attr['team'] ) ) {
		$calculations .= "			SUM(g.ergebnis) AS pins,
										COUNT(g.ergebnis) AS anzahl,
										ROUND(SUM(g.ergebnis)/COUNT(g.ergebnis),3) AS schnitt,
										tbl_hsp.maxsp AS hsp,
										tbl_hser.maxser AS hser,
										s.klss_ssn_id,
										s.sktn_klss_ssn_id,";
		$join .= "			INNER JOIN
								(SELECT
									h.hid,
									MAX(h.pins) AS maxsp
									FROM	(SELECT
												s.sktn_klss_ssn_id AS hid,
												g.nummer AS nr,
												SUM(g.ergebnis) AS pins
												FROM $wpdb->sktn_klss_ssn AS s
												LEFT JOIN $wpdb->sektion AS sn ON sn.sktn_id = s.sktn_id
												INNER JOIN $wpdb->spiel AS g ON g.sktn_klss_ssn_id = s.sktn_klss_ssn_id
												WHERE reserve = 0
												GROUP BY g.runde,g.nummer,s.sktn_klss_ssn_id
												ORDER BY s.sktn_klss_ssn_id DESC) AS h
									GROUP BY h.hid) AS tbl_hsp ON tbl_hsp.hid = s.sktn_klss_ssn_id
							INNER JOIN  
									(SELECT
										h.hid,
										MAX(h.pins) AS maxser
										FROM	(SELECT
													s.sktn_klss_ssn_id AS hid,
													g.runde AS rd,
													SUM(g.ergebnis) AS pins
													FROM $wpdb->sktn_klss_ssn AS s
													LEFT JOIN $wpdb->sektion AS sn ON sn.sktn_id = s.sktn_id
													INNER JOIN $wpdb->spiel AS g ON g.sktn_klss_ssn_id = s.sktn_klss_ssn_id
													WHERE reserve = 0
													GROUP BY g.runde,s.sktn_klss_ssn_id
													ORDER BY s.sktn_klss_ssn_id DESC) AS h
										GROUP BY h.hid) AS tbl_hser ON tbl_hser.hid = s.sktn_klss_ssn_id";
		$groupby = 'g.sktn_klss_ssn_id';
	}


	// neue adaptierung - überschreibt wenn notwendig Dinge von vorher -> Ziel optimierung ...
	switch ( $output ) {

		case 'bewerb':
			$join   = 'FROM ' . $wpdb->$output . ' AS b';
			$fields = '*, b.name as bewerb,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// $orderby = $output.'.name';
			break;

		case 'saison':
			$join   = 'FROM ' . $wpdb->$output . ' AS sa';
			$fields = '*, sa.name as saison,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// $orderby = $output.'.name';
			break;

		case 'verein':
			$join   = 'FROM ' . $wpdb->$output . ' AS v';
			$fields = '*, v.name as verein,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// $orderby = $output.'.name';
			break;

		case 'sektion':
			$join   = 'FROM ' . $wpdb->$output . ' AS s';
			$fields = '*, s.name as sektion,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// $orderby = $output.'.name';
			break;

		case 'klasse':
			$join   = 'FROM ' . $wpdb->$output . ' AS k';
			$fields = '*, k.name as klasse,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// $orderby = $output.'.name';
			break;

		case 'klss_ssn':
			$join   = 'FROM ' . $wpdb->$output . ' AS k
					LEFT JOIN ' . $wpdb->klasse . ' AS kn ON kn.klss_id = k.klss_id
					LEFT JOIN ' . $wpdb->saison . ' AS sa ON sa.ssn_id = k.ssn_id
					LEFT JOIN ' . $wpdb->bewerb . ' AS b ON b.bwrb_id = kn.bwrb_id';
			$fields = '*, kn.name as klasse, sa.name as saison, b.name as bewerb,'; // "$output.$col, $output.name"; optimieren ? -> $col war die übergebenen spalte ….
			// $orderby = $output.'.name';
			break;

		case 'sktn_klss_ssn':
			$join = "FROM $wpdb->sektion AS sn
					LEFT JOIN $wpdb->sktn_klss_ssn AS s ON s.sktn_id = sn.sktn_id
					INNER JOIN $wpdb->verein AS v ON v.vrn_id = sn.vrn_id
					INNER JOIN $wpdb->klss_ssn AS k ON k.klss_ssn_id = s.klss_ssn_id
					LEFT JOIN $wpdb->klasse AS kn ON kn.klss_id = k.klss_id
					LEFT JOIN $wpdb->saison AS sa ON sa.ssn_id = k.ssn_id
					LEFT JOIN $wpdb->bewerb AS b ON b.bwrb_id = kn.bwrb_id
					LEFT JOIN $wpdb->splr_sktn_klss_ssn AS z ON z.sktn_klss_ssn_id = s.sktn_klss_ssn_id"; // zur ermittlung der Mitgliederanzahl


			$fields  = "*, s.sktn_klss_ssn_id, sn.name AS sektion, v.name AS verein, kn.name AS klasse, sa.name as saison, b.name as bewerb, COUNT(splr_id) AS anzahl,";
			$groupby = "s.sktn_klss_ssn_id";
			// $orderby = '';
			break;

		case 'spieler':
			$join   = "FROM $wpdb->spieler AS p
					INNER JOIN $wpdb->verein AS v ON v.vrn_id = p.vrn_id
					LEFT JOIN $wpdb->splr_sktn_klss_ssn AS z ON p.splr_id = z.splr_id
					LEFT JOIN $wpdb->sktn_klss_ssn AS s ON s.sktn_klss_ssn_id = z.sktn_klss_ssn_id
					LEFT JOIN $wpdb->sektion AS sn ON sn.sktn_id = s.sktn_id
					LEFT JOIN $wpdb->klss_ssn AS k ON k.klss_ssn_id = s.klss_ssn_id
					LEFT JOIN $wpdb->klasse AS kn ON kn.klss_id = k.klss_id";
			$fields = "p.splr_id,p.vrn_id,s.sktn_klss_ssn_id,k.klss_ssn_id,p.vorname,p.nachname,p.sex,v.name AS verein,sn.name AS sktn_klss_ssn, kn.name AS klss_ssn,";
			break;
		default:
	}

	// $result = $wpdb->get_results("SELECT $select FROM $from WHERE $where GROUP BY $table.$col ORDER BY $table.name");

	// Sortierung @todo
	if ( ! empty( $groupby ) ) {
		$groupby = 'GROUP BY ' . $groupby;
	}

	if ( ! empty( $orderby ) ) {
		$orderby = 'ORDER BY ' . $orderby;
	}

	if ( ! empty( $where ) ) {
		$where = 'WHERE 1=1' . $where;
	}

	// @todo letzten Beistrich bei Calculations und/oder bei Fields...? Workaround derzeit 1+1 ^^ todo $from einführen ^^
	$result = $wpdb->get_results( "SELECT $found_rows $fields $calculations 1+1 $from $join $where $groupby $orderby $limits " );

	// @debug:
	// $debug = false;
	$debug = false;
	if ( true == $debug && current_user_can( 'manage_options' ) ) {

		print_bwdb( $attr );
		$wpdb->print_error;
		echo $wpdb->last_query;
		print_bwdb( $result );
		echo "<br /><hr />";
	}


	return $result;


	/******************************************************************************
	 * OBJEKT-AUSGABEN
	 * ->vorname        Vorname
	 * ->nachname        Nachname
	 * ->splr_id        Passnummer
	 * ->verein        Vereinsname
	 * ->pins            Gespielte Pins Insgesamt
	 * ->maxspl        Höchstes Spiel
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