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

require_once ('../../hoam-startup.php');

session_start();

// No security check is performed, there is nothing secretive here.

if (isset ($_GET['id'])) {
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$violation_class = new HOAM_violation ('violations');
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');
	
	// Just get info for the specified severity
	$trusted_severity = (string) $violation_severity_class -> superClean ($_GET['id'], $violation_severity_class -> returnColumnSize ('id'));

	$current_date = getdate ();
	if (isset ($_GET['resolveby']) AND ($_GET['resolveby'] != '')) {
		$numdays = (int) ceil (((strtotime ($_GET['resolveby']) - $current_date[0]) / 86400));
		$dateresolveby = HOAM_date ($_HOAM_country ['date']['format_long'] , $current_date[0] + $numdays * 60 * 60 * 24);
	} else {
		$numdays = $violation_severity_class -> returnNumDays ($trusted_severity);
		$dateresolveby = HOAM_date ($_HOAM_country ['date']['format_long'] , $current_date[0] + (($_HOAM_setting ['violation']['days_to_pad_resolveby'] + $numdays) * 60 * 60 * 24));
	}
	if (isset ($_GET['address'])) {
		$lot_id = $lot_class -> superClean ($_GET['address'], $lot_class -> returnColumnSize ('id'));
	} else {
		$lot_id = NULL;
	}
	if (isset ($_GET['dateviolation'])) {
		$dateviolation = HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($_GET['dateviolation']));
	} else {
		$dateviolation = HOAM_date ($_HOAM_country ['date']['format_long']);
	}

	if (isset ($_GET['category'])) {
		$category_list = explode (',', $_GET['category']);
		$trusted_category_list = array ();
		foreach ($category_list as $category) {
			$clean_category = $violation_category_class -> superClean ($category, $violation_category_class -> returnColumnSize ('category'));
			if ($violation_category_class -> idExists ($clean_category)) {
				array_push ($trusted_category_list, $clean_category);
			}
		}
	} else {
		$trusted_category_list = NULL;
	}
	
	// Need to search through the previous violations and see when was the last
	// time a violation was sent for a specific category. Eg, rather than do a
	// generic test when the last violation was sent, if we're entering a
	// violation for a yard that needs maintenance don't say it was previously
	// noted for a violation about a fence that needed repair.
	//
	// 1) Get the list of previous violations at the residence.
	// 2) Make sure this is for the current homeowner.
	// 3) Make sure this violation was within the last two years.
	// 4) Compare and see if the categories listed on the previous violation
	//    match what we're trying to enter now.
	// 5) Push the date of the violations we found to variable.
	// 6) If we don't have anything, then set dateprevious to '##/##/####' and
	//    enclose in an 'error' span.
	$recent_violation_list = $violation_class -> listRecentViolation ($lot_id);
	if (is_array ($recent_violation_list)) {
		$dateprevious = HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($violation_class -> returnViolationDate ($violation_class -> returnRecentViolation ($lot_id))));
		$two_years_ago = getdate ();
		$two_years_ago = ($two_years_ago['year'] - 2) . '-' . $two_years_ago['mon'] . '-' . $two_years_ago['mday']; 
		foreach ($recent_violation_list as $violation) {
			$current_homeowner = $homeowner_class -> returnCurrentOwner ($lot_id);
			$current_homeowner_purchasedate = $homeowner_class -> returnPurchaseDate ($current_homeowner);
			$violation_date = $violation_class -> returnViolationDate ($violation);
			if (($violation_date >= $current_homeowner_purchasedate) AND ($violation_date >= date ($_HOAM_country ['date']['format_mysql'], strtotime ($two_years_ago)))) {
				$violation_category_list = unserialize ($violation_class -> returnCategory ($violation));
				if (is_array ($violation_category_list)) {
					foreach ($violation_category_list as $violation) {
						if (in_array ($violation, $trusted_category_list)) {
							$previousnotice = ' <strong>Please note, you previously received information for this same violation on ' . HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($violation_date)) . '</strong>. ';
							// We found something, just quit.
							break (2);
						}
					}
				} else {
					$previousnotice = '';
				}
			} else {
				$previousnotice = '';
			}
		}
	} else {
		$previousnotice = '';
	}

	$daysresolve = '';
	
	$replace = array (
		'/\[~date-resolveby~\]/' => $dateresolveby,
	    '/\[~date-violation~\]/' => $dateviolation,
	    // There is a problem with using HOAM_returnCurrency instead of
	    // number_format here because of the way preg_replace handles the '$'
	    // character (thinking there is a replacement). It might be possible to
	    // modify the check so that we escape the '$' when necessary, but that's
	    // a low priority for the time being.
		'/\[~fine-day~\]/' => number_format ($violation_severity_class -> returnFinePerDay ($trusted_severity), 2),
		'/\[~fine-notice~\]/' => number_format ($violation_severity_class -> returnFinePerNotice ($trusted_severity), 2),
		'/\[~numdays~\]/' => $numdays,
		'/\[~numdays([+\-]\d{1,2})~\]/' => $daysresolve,
		'/\[~previous-notice~\]/' => $previousnotice,
	);
	
	$preamble = $violation_severity_class -> returnPreamble ($trusted_severity);
	$preamble_updated = preg_replace (array_keys ($replace), array_values ($replace), $preamble);
	$closing = $violation_severity_class -> returnClosing ($trusted_severity);
	$closing_updated = preg_replace (array_keys ($replace), array_values ($replace), $closing);

	$xw = xmlwriter_open_memory ();
	xmlwriter_set_indent ($xw, 1);
	xmlwriter_set_indent_string ($xw, ' ');
	xmlwriter_start_document ($xw, '1.0', 'UTF-8');
	// A first element
	xmlwriter_start_element ($xw, 'ajax-response');
	// The second parameter is for tag attributes
	// This is done using an associative array
	xmlwriter_start_element ($xw, 'item');
	xmlwriter_write_element ($xw, 'id', $trusted_severity);
	// It's pretty likely that the various severity descriptions will contain HTML, so we'll just enclose them all in CDATA tags
	xmlwriter_start_element ($xw, 'assume_resolved');
	xmlwriter_write_cdata ($xw, (int) $violation_severity_class -> isBitSet ($trusted_severity, $_HOAM_setting ['violation']['flags']['assume_resolved']));
	xmlwriter_end_element ($xw); // assume_resolved
	xmlwriter_start_element ($xw, 'closing');
	xmlwriter_write_cdata ($xw, stripslashes ($closing));
	xmlwriter_end_element ($xw); // closing
	xmlwriter_start_element ($xw, 'closing_updated');
	xmlwriter_write_cdata ($xw, stripslashes (str_replace ('\r\n', "\r\n", $closing_updated)));
	xmlwriter_end_element ($xw); // closing_updated
	xmlwriter_start_element ($xw, 'escalate');
	xmlwriter_write_cdata ($xw, $violation_severity_class -> returnSeverity ($violation_severity_class -> returnEscalate ($trusted_severity)));
	xmlwriter_end_element ($xw); // escalate
	xmlwriter_start_element ($xw, 'escalate_id');
	xmlwriter_write_cdata ($xw, $violation_severity_class -> returnEscalate ($trusted_severity));
	xmlwriter_end_element ($xw); // escalate_id
	xmlwriter_start_element ($xw, 'fine_interest');
	xmlwriter_write_cdata ($xw, $violation_severity_class -> returnFineInterest ($trusted_severity));
	xmlwriter_end_element ($xw); // fine_interest
	xmlwriter_start_element ($xw, 'fine_per_day');
	xmlwriter_write_cdata ($xw, $violation_severity_class -> returnFinePerDay ($trusted_severity));
	xmlwriter_end_element ($xw); // fine_per_day
	xmlwriter_start_element ($xw, 'fine_per_notice');
	xmlwriter_write_cdata ($xw, $violation_severity_class -> returnFinePerNotice ($trusted_severity));
	xmlwriter_end_element ($xw); // fine_per_notice
	if ($daysresolve) {
		xmlwriter_start_element ($xw, 'numdays');
		xmlwriter_write_cdata ($xw, $daysresolve);
		xmlwriter_end_element ($xw); // numdays
	} else {
		xmlwriter_start_element ($xw, 'numdays');
		xmlwriter_write_cdata ($xw, $numdays);
		xmlwriter_end_element ($xw); // numdays
	}
	xmlwriter_start_element ($xw, 'preamble');
	xmlwriter_write_cdata ($xw, stripslashes ($preamble));
	xmlwriter_end_element ($xw); // preamble
	xmlwriter_start_element ($xw, 'preamble_updated');
	xmlwriter_write_cdata ($xw, stripslashes (str_replace ('\r\n', "\r\n", $preamble_updated)));
	xmlwriter_end_element ($xw); // preamble_updated
	xmlwriter_start_element ($xw, 'severity');
	xmlwriter_write_cdata ($xw, $violation_severity_class -> returnSeverity ($trusted_severity));
	xmlwriter_end_element ($xw); // severity
	xmlwriter_end_element ($xw); // item
	
	// Generate the XML file and echo it back to the client
	xmlwriter_end_element ($xw); // ajax-response
	xmlwriter_end_document ($xw);
	header ('Content-type: text/xml');
	echo xmlwriter_output_memory ($xw);
} else {
	// We don't know what action we're being asked to perform
	header ('Location: /error/unknown_action/');
}

exit ();

?>