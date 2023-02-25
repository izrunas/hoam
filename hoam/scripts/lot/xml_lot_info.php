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
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_GET['lot_id'])) {
		require_once (HOAM_PATH_FUNCTION . 'homeowner.php');

		$homeowner_class = new HOAM_homeowner ('homeowners');
		$lot_class = new HOAM_lot ('lots');
				
		$trusted_lot_id = (int) $lot_class -> superClean ($_GET['lot_id'], $lot_class -> returnColumnSize ('id'));
		$current_homeowner = $homeowner_class -> returnCurrentOwner ($trusted_lot_id);
		$previous_homeowner = $homeowner_class -> returnPreviousOwnerId ($trusted_lot_id);
		
		$xw = xmlwriter_open_memory ();
		xmlwriter_set_indent ($xw, 1);
		xmlwriter_set_indent_string ($xw, ' ');
		xmlwriter_start_document ($xw, '1.0', 'UTF-8');
		// A first element
		xmlwriter_start_element ($xw, 'ajax-response');

		xmlwriter_start_element ($xw, 'property');
		xmlwriter_write_element ($xw, 'id', $trusted_lot_id);
		xmlwriter_write_element ($xw, 'lot', $lot_class -> returnLot ($trusted_lot_id));
		xmlwriter_write_element ($xw, 'block', $lot_class -> returnBlock ($trusted_lot_id));
		xmlwriter_write_element ($xw, 'address', $lot_class -> returnAddress ($trusted_lot_id));
		xmlwriter_write_element ($xw, 'street', $lot_class -> returnStreet ($trusted_lot_id));
		xmlwriter_write_element ($xw, 'current_homeowner_id', $current_homeowner);
		xmlwriter_start_element ($xw, 'current_homeowner_address');
		xmlwriter_write_cdata ($xw, HOAM_homeownerReturnMailingAddress ($current_homeowner));
		xmlwriter_end_element ($xw); // current_homeowner_address
		xmlwriter_start_element ($xw, 'current_homeowner_name');
		xmlwriter_write_cdata ($xw, $homeowner_class -> returnName ($current_homeowner));
		xmlwriter_end_element ($xw); // current_homeowner_name
		xmlwriter_write_element ($xw, 'previous_homeowner_id', $previous_homeowner);
		xmlwriter_start_element ($xw, 'previous_homeowner_name');
		xmlwriter_write_cdata ($xw, $homeowner_class -> returnName ($previous_homeowner));
		xmlwriter_end_element ($xw); // previous_homeowner_name

		xmlwriter_end_element ($xw); // property
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
}

exit ();

?>