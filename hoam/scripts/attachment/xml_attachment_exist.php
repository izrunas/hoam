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

// We're using a slightly different permissions check on this, to make sure that
// unauthorized users can't view all of the attachments. Normally we'd check
// against the attachment_view group, but because (by default) members of the
// registered users group can view attachments, we don't want someone to be able
// to view all attachments.
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_view'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['website']['users']['ids']['root'] === $_SESSION['current_user']['id']))) {

	if (isset ($_GET['owner'])) {
		$attachment_class = new HOAM_attachments ('attachments');
		$trusted_owner = $attachment_class -> superClean ($_GET['owner'], $attachment_class -> returnColumnSize ('id'));

		$xw = xmlwriter_open_memory ();
		xmlwriter_set_indent ($xw, 1);
		xmlwriter_set_indent_string ($xw, ' ');
		xmlwriter_start_document ($xw, '1.0', 'UTF-8');
		// A first element
		xmlwriter_start_element ($xw, 'ajax-response');
		
		$attachment_list = $attachment_class -> listWithOwner ($trusted_owner);
	
		if (is_array ($attachment_list)) {
			foreach ($attachment_list as $attachment) {
				xmlwriter_start_element ($xw, 'attachment');
				xmlwriter_write_element ($xw, 'id', $attachment);
				xmlwriter_end_element ($xw); // attachment
			}
		}
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
}

exit ();

?>