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

$group_members_class = new HOAM_group_members ('group_members');

if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_approve'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['users']['ids']['root'] === $_SESSION['current_user']['id']))) {
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$violation_class = new HOAM_violation ('violations');
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');
	
	if (isset ($_GET['lot'])) {
		$trusted_lot = (int) $lot_class -> superClean ($_GET['lot'], $lot_class -> returnColumnSize ('id'));
		if ($lot_class -> idExists ($trusted_lot)) {
			$current_homeowner = $homeowner_class -> returnCurrentOwner ($trusted_lot);
			$all_violations = $violation_class -> listLotId ($trusted_lot);
			$homeowner_violations = $violation_class -> ListAfterdate ($trusted_lot, $homeowner_class -> returnPurchasedate ($current_homeowner));

			$xw = xmlwriter_open_memory ();
			xmlwriter_set_indent ($xw, 1);
			xmlwriter_set_indent_string ($xw, ' ');
			xmlwriter_start_document ($xw, '1.0', 'UTF-8');
			// A first element
			xmlwriter_start_element ($xw, 'ajax-response');

			if (is_array ($all_violations)) {
				foreach ($all_violations as $violation) {
					// The second parameter is for tag attributes
					// This is done using an associative array
					xmlwriter_start_element ($xw, 'violation');
					
					xmlwriter_write_element ($xw, 'id', $violation);
					xmlwriter_write_element ($xw, 'date_resolution', $violation_class -> returnResolutionDate ($violation));
					xmlwriter_write_element ($xw, 'date_resolveby', $violation_class -> returnResolveByDate ($violation));
					xmlwriter_write_element ($xw, 'date_violation', $violation_class -> returnViolationDate ($violation));
					// Category is currently broken since it hasn't been updated since the switch to multiple categories
					// $xml -> createTag ('category', array(), $violation_category_class -> returnCategory ($violation_class -> returnCategory ($violation)), $current_tag);
					xmlwriter_write_element ($xw, 'severity', $violation_severity_class -> returnSeverity ($violation_class -> returnSeverity ($violation)));
					if (is_array ($homeowner_violations) AND in_array ($violation, $homeowner_violations)) {
						xmlwriter_write_element ($xw, 'current_homeowner', '1');
						$tablerow = '<tr>';
					} else {
						xmlwriter_write_element ($xw, 'current_homeowner', '0');
						$tablerow = '<tr class="less-opacity">';
					}
					$tablerow .= '<td class="center">' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_class -> returnViolationDate ($violation))) . '</td>';
					$tablerow .= '<td class="center">' . $violation_severity_class -> returnSeverity ($violation_class -> returnSeverity ($violation)) . '</td>';
					$tablerow .= '<td><ul style="list-style: none">';
					$category_list = unserialize ($violation_class -> returnCategory ($violation));
					if (is_array ($category_list)) {
						foreach ($category_list as $category) {
							$tablerow .= '<li>' . $violation_category_class -> returnCategory ($category). '</li>';
						}
					}
					$tablerow .= '</ul></td>';
					$tablerow .= '<td class="center">' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_class -> returnResolveByDate ($violation))) . '</td>';
					$resolution_date = $violation_class -> returnResolutionDate ($violation);
					if ($resolution_date) {
						$resolution_date = date ($_HOAM_country ['date']['format_compare'], strtotime ($resolution_date));
						$current_date = date ($_HOAM_country ['date']['format_compare']);
						switch (TRUE) {
							case (($resolution_date > $current_date) OR ($resolution_date < date ($_HOAM_country ['date']['format_compare'], strtotime ($violation_class -> returnViolationDate ($violation))))) :
								$tablerow .= '<td class="center error">';
								break;
							case ($resolution_date == $current_date) :
								$tablerow .= '<td class="center caution">';
								break;
							case ($resolution_date < $current_date) :
								$tablerow .= '<td class="center complete">';
								break;
							default:
								$tablerow .= '<td class="center">';
						}
						$tablerow .= HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime (preg_replace ('/-/', '/', $violation_class -> returnResolutionDate ($violation)))) . '</td></tr>';
					} else {
						$tablerow .= '<td class="center warning">' . $_HOAM_language ['errors']['violation']['not-resolved'] . '</td></tr>';
					}
					xmlwriter_start_element ($xw, 'tablerow');
					xmlwriter_write_cdata ($xw, $tablerow);
					xmlwriter_end_element ($xw); // tablerow
					
					xmlwriter_end_element ($xw); // violation
				}
			}
			// Generate the XML file and echo it back to the client
			xmlwriter_end_element ($xw); // ajax-response
			xmlwriter_end_document ($xw);
			header ('Content-type: text/xml');
			echo xmlwriter_output_memory ($xw);
		} else {
			// We don't know what action we're being asked to perform
			header ('Location: /error/unknown_id/');
		}

	} else {
		// We don't know what action we're being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
}

exit ();

?>