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
 
if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {

	if (isset ($_GET['complete'])) {
		$new_id = $_HOAM_log -> add ('Upgrade complete.', HOAM_MESSAGE_NOTICE, $_HOAM_setting ['log']['flags']['upgrade']);
	}

	$xw = xmlwriter_open_memory ();
	xmlwriter_set_indent ($xw, 1);
	xmlwriter_set_indent_string ($xw, ' ');
	xmlwriter_start_document ($xw, '1.0', 'UTF-8');
	// A first element
	xmlwriter_start_element ($xw, 'ajax-response');

	$last_completed = $_HOAM_log -> findMessage ('Upgrade_complete.', $_HOAM_setting ['log']['flags']['upgrade']);
	if (is_array ($last_completed)) {
		if ($last_completed[0] == $new_id) {
			$last_compeleted = $last_completed[1];
		}
	} else {
		$last_completed = 0;
	}
	$last_sequence = $_HOAM_log -> returnSequence ($last_completed);
	// OK, we're going to cheat a little and not create a whole new function for this.
	$upgrade_log = $_HOAM_log -> listBitSet ($_HOAM_setting ['log']['flags']['upgrade'], "sequence >= $last_sequence AND flags", 0, 0, 'sequence ASC');
	if (is_array ($upgrade_log)) {
		$logs = '';
		xmlwriter_start_element ($xw, 'log');
		foreach ($upgrade_log as $log) {
			$log_detail = $_HOAM_log -> listAll ($log);
			$logs .= '<tr><td>' . $log_detail[0]['datecreated'] . '</td><td>' . $log_detail[0]['message'] . '</td></tr>'; 
		}
		$table = '<table>' . $logs . '</table>';
		xmlwriter_write_cdata ($xw, $table);
		xmlwriter_end_element ($xw); // log
	} else {
		xmlwriter_write_element ($xw, 'log', '');
	}

	// Generate the XML file and echo it back to the client
	xmlwriter_end_element ($xw); // ajax-response
	xmlwriter_end_document ($xw);
	header ('Content-type: text/xml');
	echo xmlwriter_output_memory ($xw);
} else {
	header ('Location: /error/403/');
}

exit ();

?>
