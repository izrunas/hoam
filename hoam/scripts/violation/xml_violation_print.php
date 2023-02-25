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
	($_HOAM_setting ['website']['users']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_GET['date'])) {
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$violation_class = new HOAM_violation ('violations');
		$wiki = new HOAM_wiki_parser ();
		
		// Are there any violations recorded on the requested date?
		$trusted_date = (string) date ($_HOAM_country ['date']['format_mysql'], strtotime ($violation_class -> superClean ($_GET['date'], $violation_class -> returnColumnSize ('dateviolation'))));
		$violations = $violation_class -> listOnViolationDate ($trusted_date);

		$xw = xmlwriter_open_memory ();
		xmlwriter_set_indent ($xw, 1);
		xmlwriter_set_indent_string ($xw, ' ');
		xmlwriter_start_document ($xw, '1.0', 'UTF-8');
		// A first element
		xmlwriter_start_element ($xw, 'list');

		// Populate the XML file with the list of addresses on the requested street.

		foreach ($violations as $violation) {
			// The second parameter is for tag attributes
			// This is done using an associative array
			xmlwriter_start_element ($xw, 'violation');
			xmlwriter_write_element ($xw, 'id', $violation);
			xmlwriter_write_element ($xw, 'violation_date', $violation_class -> returnViolationDate ($violation));
			$letter = stripslashes (str_replace ('\n', "\n", str_replace ('\r\n', "\r\n", $wiki -> parse ($violation_class -> returnOriginalLetter ($violation)))));
			// This is an ugly fix for the existing letters that ended up with
			// high-ascii garbage in the header.
			// Once all of the code is updated and the existing letters fixed,
			// this can be removed.
			$letter = preg_replace ('/[^\x00-\x7f]{3,}/', '&bull;', $letter);
			xmlwriter_start_element ($xw, 'original_letter');
			xmlwriter_write_cdata ($xw, $letter);
			xmlwriter_end_element ($xw); // original_letter
			xmlwriter_end_element ($xw); // violation
			
			// If a rental, create a duplicate for the resident
			$current_homeowner = $homeowner_class -> returnCurrentOwner ($violation_class -> returnLotId ($violation));
			// If this homeowner does NOT receive mail at their residence,
			// should we send a copy to the residence?
			if ((!$homeowner_class -> isBitSet ($current_homeowner, $_HOAM_setting ['homeowner']['flags']['residence_mailing'])) AND
				$_HOAM_setting ['homeowner']['send_current_resident']) {
				xmlwriter_start_element ($xw, 'violation');
				xmlwriter_write_element ($xw, 'id', $violation);
				xmlwriter_write_element ($xw, 'violation_date', $violation_class -> returnViolationDate ($violation));
				$letter = stripslashes (str_replace ('\n', "\n", str_replace ('\r\n', "\r\n", $wiki -> parse ($violation_class -> returnOriginalLetter ($violation)))));
				xmlwriter_start_element ($xw, 'original_letter');
				xmlwriter_write_cdata ($xw, $letter);
				xmlwriter_end_element ($xw); // original_letter
				xmlwriter_end_element ($xw); // violation
			}
		}
		// Generate the XML file and echo it back to the client
		xmlwriter_end_element ($xw); // list
		xmlwriter_end_document ($xw);
		header ('Content-type: text/xml');
		echo xmlwriter_output_memory ($xw);
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