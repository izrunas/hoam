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

// There's no reason to secure this information, there's nothing sensitive.
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_GET['lotid'])) {
		$budget_class = new HOAM_budget ('budget');
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$homeowner_sale_class = new HOAM_homeowner_sale ('homeowner_sale');
		$lot_class = new HOAM_lot ('lots');
		
		$trusted_id = $lot_class -> superClean ($_GET['lotid'], $lot_class -> returnColumnSize ('id'));
		$xw = xmlwriter_open_memory ();
		xmlwriter_set_indent ($xw, 1);
		xmlwriter_set_indent_string ($xw, ' ');
		xmlwriter_start_document ($xw, '1.0', 'UTF-8');
		// A first element
		xmlwriter_start_element ($xw, 'ajax-response');

		xmlwriter_start_element ($xw, 'item');
		$homeowner_id = $homeowner_class -> returnCurrentOwner ($trusted_id);
		if ($homeowner_id) {
			xmlwriter_write_element ($xw, 'current_exists', '1');
			if ($budget_class -> returnAccountBalance ($homeowner_id) > 0) {
				xmlwriter_write_element ($xw, 'open_charges', '1');
			} else {
				xmlwriter_write_element ($xw, 'open_charges', '0');
			}
			if ($homeowner_sale_class -> listSimpleQuery ("SELECT id FROM homeowner_sale WHERE lot_id='$trusted_id' AND ~flags & " . $_HOAM_setting ['homeowner']['flags']['residence_sold'])) {
				$xml -> createTag ('homeowner_sale', array(), '1', $current_tag);
			} else {
				xmlwriter_write_element ($xw, 'homeowner_sale', '0');
			}
		} else {
			xmlwriter_write_element ($xw, 'current_exists', '0');
		}

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
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

exit ();

?>