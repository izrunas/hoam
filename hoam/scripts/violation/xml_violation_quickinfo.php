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

/*
*
* This file returns an XML document with a pre-formatted HTML blurb suitable for
* usw when displaying recent violation information about a particular residence.
*
*/

session_start();

$group_members_class = new HOAM_group_members ('group_members');

if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_approve'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['users']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_GET['lot'])) {
		require_once (HOAM_PATH_FUNCTION. 'violations.php');

		$homeowner_class = new HOAM_homeowner ('homeowners');
		$attachment_class = new HOAM_attachments ('attachments');
		$lot_class = new HOAM_lot ('lots');
		$violation_class = new HOAM_violation ('violations');
		$violation_category_class = new HOAM_violation_category ('violation_category');
		$violation_severity_class = new HOAM_violation_severity ('violation_severity');
		
		$trusted_lot = (int) $lot_class -> superClean ($_GET['lot'], $lot_class -> returnColumnSize ('id'));
		if ($lot_class -> idExists ($trusted_lot)) {
			// Eventually this needs to be modified to look for the actual front-view of the residence.
			// Currently, it looks for the oldest attachment for the residence, which has tended to be the front view
			// only because they were entered into the system that way.
			$lot_front_view = $attachment_class -> ListWithOwner ($trusted_lot, $_HOAM_setting ['attachment']['flags']['lot']);
			if ($lot_front_view) {
				$lot_front_view = $lot_front_view[0];
			}
			$current_homeowner_id = $homeowner_class -> returnCurrentOwner ($trusted_lot);

			$recent_violation_id = $violation_class -> returnRecentViolation ($trusted_lot);
			// If we found a violation, is it before / after the current homeowner purchased the residence?
			if ($homeowner_class -> returnPurchaseDate ($current_homeowner_id) <= $violation_class -> returnViolationDate ($recent_violation_id)) {
				// What violations were found during the most recent report?
				$recent_violation_categories = unserialize ($violation_class -> returnCategory ($recent_violation_id));
				// Was this the 1st, 2nd, 3rd (etc.) report?
				$recent_violation_severity = $violation_severity_class -> returnSeverity ($violation_class -> returnSeverity ($recent_violation_id));
				if (is_array ($recent_violation_categories)) {
					foreach ($recent_violation_categories as $this_category) {
						$recent_violation_category_string .= '<li>' . $violation_category_class -> returnCategory ($this_category) . '</li>';
					}
				}
			}
			
			// Start assembling all of the information.
			$quickinfo = NULL;
			if ($lot_front_view) {
				$quickinfo .= '<img src="/hoam/scripts/attachment/view.php?id=' . $lot_front_view . '" alt="' . $attachment_class -> returnFilename ($lot_front_view) . '" height="120" width="160" />';
			}
			$quickinfo .= stripslashes ($homeowner_class -> returnName ($current_homeowner_id));
			// Append the owner | lease / rent status of the residence
			if ($homeowner_class -> isBitSet ($current_homeowner_id, $_HOAM_setting ['homeowner']['flags']['resident'])) {
				$quickinfo .= ' (<abbr title="' . $_HOAM_language ['abbr']['h'] . '">H</abbr>)<br />';
			} else {
				$quickinfo .= ' (<abbr title="' . $_HOAM_language ['abbr']['lr'] . '">L/R</abbr>)<br />';
			}
			$quickinfo .= 'Purchased: ' . date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_class -> returnPurchaseDate ($current_homeowner_id))) . '<br /><br />';
			// If there was a violation, show the reasons
			if (isset ($recent_violation_categories)) {
				$quickinfo .= '<abbr title="' . $_HOAM_language ['abbr']['lrv'] . '">LRV</abbr>: ' . date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_class -> returnViolationDate ($recent_violation_id))) . ' (Severity '. $recent_violation_severity . ')<br />';
				$quickinfo .= 'Issues Reported Last Violation:<ul>' . $recent_violation_category_string . '</ul>';
				$quickinfo .= 'Recommended Severity Level: <span class="error">' . $violation_severity_class -> returnSeverity (HOAM_violationRecommendedSeverity ($recent_violation_id)) . '</span>';
			} else {
				$quickinfo .= $_HOAM_language ['errors']['violation']['history_none'] . '<br/><br/>';
				$quickinfo .= 'Recommended Severity Level: <span class="error">1</span>';
			}
			if ($homeowner_class -> isBitSet ($current_homeowner_id, $_HOAM_setting ['homeowner']['flags']['ignore_violations'])) {
				$exempt_prior = '1';
				$quickinfo .= '<br /><br /><span class="warning">' . $_HOAM_language ['errors']['violation']['homeowner-exempt'] . '</span>';
			} else {
				$exempt_prior = '0';
			}
			$purchase_date = strtotime ($homeowner_class -> returnPurchaseDate ($current_homeowner_id));
			// Only bother with this check if the owner isn't exempt from a
			// prior violation. 
			if (($exempt_prior == 0) AND (time () < $purchase_date + ($_HOAM_setting ['violation']['grace_after_purchase'] * 86400))) {
				$exempt_grace = 1;
			} else {
				$exempt_grace = 0;
			}

			$xw = xmlwriter_open_memory ();
			xmlwriter_set_indent ($xw, 1);
			xmlwriter_set_indent_string ($xw, ' ');
			xmlwriter_start_document ($xw, '1.0', 'UTF-8');
			// A first element
			xmlwriter_start_element ($xw, 'ajax-response');

			xmlwriter_start_element ($xw, 'item');
			
			xmlwriter_write_element ($xw, 'exempt_grace', $exempt_grace);
			xmlwriter_write_element ($xw, 'exempt_prior', $exempt_prior);
			xmlwriter_start_element ($xw, 'recommended_severity');
			xmlwriter_write_cdata ($xw, HOAM_violationRecommendedSeverity ($recent_violation_id));
			xmlwriter_end_element ($xw); // recommended_severity
			xmlwriter_start_element ($xw, 'quickinfo');
			xmlwriter_write_cdata ($xw, $quickinfo);
			xmlwriter_end_element ($xw); // recommended_severity

			xmlwriter_end_element ($xw); // item
			
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