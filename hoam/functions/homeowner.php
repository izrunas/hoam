<?php
/*
 * This file is part of HOAM, copyright (C) 2002-2020 Robert Butler
 *
 * HOAM is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 *
 * HOAM is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with HOAM; if not, see http://www.gnu.org/licenses or write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301
 *
 * Questions specific to HOAM should be directed to ARP Realty. Please see
 * the HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

function HOAM_homeownerReturnMailingAddress ($id) {
	global $_HOAM_country, $_HOAM_language;
	
	$homeowner_class = new HOAM_homeowner ('homeowners');
	
	// Did we get a valid #id to look for?
	if ($homeowner_class -> idExists ($id)) {
		// Yep. Display info about that item.
		$homeowner_information = $homeowner_class -> listAll ($id);
		$homeowner_information = $homeowner_information[0];
		if (!empty($homeowner_information['address1'])) {
			$HTML  = html_encode (stripslashes ($homeowner_information['address1'])) . '<br />';
		}
		if (!empty($homeowner_information['address2'])) {
			$HTML .= html_encode (stripslashes ($homeowner_information['address2'])) . '<br />';
		}
		if (!empty($homeowner_information['address3'])) {
			$HTML .= html_encode (stripslashes ($homeowner_information['address3'])) . '<br />';
		}
		$HTML .= html_encode (stripslashes ($homeowner_information['city'])) . ', ';
		$HTML .= $homeowner_information['state'] . ' ';
		$HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_information['zipcode'], 1);
	} else {
		$HTML = '<p>' . $_HOAM_language ['errors']['homeowner']['unknown_id'] . '</p>';
	}
	return (stripslashes ($HTML));
}

function HOAM_homeownerRentalStreet () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	require_once (HOAM_PATH_FUNCTION . 'image.php');
	
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	
	$rental_chart = array ();
	$lot_count = $rental_count = 0;
	$colgroup = '<colgroup>';
	$colgroup .= '<col width="50%" />';
	$colgroup .= '<col span="3" />';
	$colgroup .= '</colgroup>';
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['lot']['street'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['lot']['number_homes'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['rental_number'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['rental_percent'] . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$tbody = '';
	$street_list = $lot_class -> ListStreets ();
	if (!empty ($street_list)) {
		// We're performing this check in case no streets have been configured.
		// This is extremely unlikely, however it will cause a MySQL error if
		// the maintenance script is set to run but no lots have been entered
		// yet (fresh install).
		$total_lot_count = 0;
		foreach ($street_list as $street) {
			$lot_list = $lot_class -> ListByStreet ($street);
			$total_lot_count += count ($lot_list);
		}		
		foreach ($street_list as $street) {
			$lot_list = $lot_class -> ListByStreet ($street);
			$lot_count = count ($lot_list);
			$rental_count = 0;
			foreach ($lot_list as $lot) {
				$current_homeowner = $homeowner_class -> returnCurrentOwner ($lot);
				if ($homeowner_class -> isBitSet ($current_homeowner, $_HOAM_setting['homeowner']['flags']['resident'])) {
				} else {
					$rental_count++;
				}
			}
			$tbody .= '<tr>';
			$tbody .= '<td>' . $street. '</td>';
			$tbody .= '<td>' . $lot_count . '</td>';
			if ($rental_count == 0) {
				$tbody .= '<td class="complete">' . $rental_count . '</td>';
				$tbody .= '<td class="complete">' . number_format (0, 2) . '%</td>';
			} else {
				$tbody .= '<td>' . $rental_count . '</td>';
				$tbody .= '<td>' . number_format (($rental_count / $lot_count) * 100, 1) . '%</td>';
				$rental_chart += array ($street => number_format (($rental_count / $total_lot_count) * 100, 1));
			}
			$tbody .= '</tr>';
		}
		if (count ($rental_chart) < 2) {
			$rental_chart += array (ucfirst ($_HOAM_language ['common']['none']) => number_format ((100 - ($rental_count / $lot_count) * 100), 1));
		}
	}
	$HTML = '<br /><center>' . HOAM_piechart ($rental_chart, 140, 140, 130, array ('fontsize' => 15)) . '<br /></center>'; 
	$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>' . $tbody . '</tbody></table>';
	return ($HTML);
}

?>
