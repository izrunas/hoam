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

session_start ();

$group_members_class = new HOAM_group_members ('group_members');

if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['tasklist_personal'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['complete']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		require_once (HOAM_PATH_PLUGIN . 'tasklist/class.php');
		require_once (HOAM_PATH_PLUGIN . 'tasklist/functions.php');
		
		$tasklist_class = new tasklist ('tasks');

		// Need to add a check for whether the current user is the same as the assigned user, or has necessary privileges.

		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['task_information'])) {
			unset ($_SESSION['task_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}
		if (isset ($_POST['cancel'])) {
			// Cancel all changes.
		} elseif (isset ($_POST['complete'])) {
			$trusted_id = $tasklist_class -> superClean ($_POST['id'], $tasklist_class -> returnColumnSize ('id'));
			// Mark the task completed.
			if ($tasklist_class -> idExists ($trusted_id)) {
				// Make sure the current user is the owner of the item, OR has admin-level privileges.
				if (($tasklist_class -> returnAssignedUser ($trusted_id) == $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id'])) {
					$tasklist_class -> setBit ($trusted_id, $_HOAM_setting ['plugin']['tasklist']['flag']['status_complete'], 'status');
					unset ($trusted_id);
				} else {
					// User does not have the necessary permissions.
					header ('Location: /error/401/');
				}
			}
		} elseif (isset ($_POST['delete'])) {
			$trusted_id = $tasklist_class -> superClean ($_POST['id'], $tasklist_class -> returnColumnSize ('id'));
			// Delete the task
			if ($tasklist_class -> idExists ($trusted_id)) {
				// Make sure the current user is the owner of the item, OR has admin-level privileges.
				if (($tasklist_class -> returnAssignedUser ($trusted_id) == $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id'])) {
					$tasklist_class -> deleteId ($trusted_id);
					unset ($trusted_id);
				} else {
					// User does not have the necessary permissions.
					header ('Location: /error/403/');
				}
			} else {
				header ('Location: /error/unknown_id/');
			}
			exit;
		} else {
			$_SESSION['task_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');

			if (isset ($_POST['id'])) {
				$trusted_id = $tasklist_class -> superClean ($_POST['id'], $tasklist_class -> returnColumnSize ('id'));
				if ($tasklist_class -> idExists ($trusted_id)) {
				} else {
					$_SESSION['error_class_item'] -> addError ('id', $_HOAM_language ['errors']['plugin']['tasklist']['unknown_id']);
				}
			}

			if (isset ($_POST['text'])) {
				$trusted_text = $tasklist_class -> superClean (urldecode ($_POST['text']), $tasklist_class -> returnColumnSize ('item'));
				if (mb_strlen ($trusted_text) < $_HOAM_setting ['plugin']['tasklist']['minimum_text_length']) {
					$_SESSION['error_class_item'] -> addError ('text', $_HOAM_language ['errors']['plugin']['tasklist']['text_too_small'], $_HOAM_setting ['plugin']['tasklist']['minimum_text_length']);
				}
			}

			// Finally! We have good data and can start modifying the database.
			//
			if (isset ($_POST['add'])) {
				$trusted_id = $tasklist_class -> add ($_SESSION['current_user']['id']);
				if ($trusted_id == FALSE) {
					// Error Adding Item
					header ('Location: /error/add/');
				}
				$tasklist_class -> changeAssignedUser ($trusted_id, $_SESSION['current_user']['id']);
			}
			$tasklist_class -> changeTask ($trusted_id, $trusted_text);
			$tasklist_class -> changeDateDue ($trusted_id);
			$tasklist_class -> changeDateModified ($trusted_id);
			//			}
		}

		// Clear the error_class_item and task_information variables
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}
	if (isset ($_SESSION['task_information'])) {
			unset ($_SESSION['task_information']);
		}

		// Send task information back to the client, whether it's been modified or not.
		$xw = xmlwriter_open_memory ();
		xmlwriter_set_indent ($xw, 1);
		xmlwriter_set_indent_string ($xw, ' ');
		xmlwriter_start_document ($xw, '1.0', 'UTF-8');
		// A first element
		xmlwriter_start_element ($xw, 'tasklist');

		// Populate the XML file with the list of addresses on the requested street.
		// The second parameter is for tag attributes
		// This is done using an associative array
		if (isset ($trusted_id)) {
			xmlwriter_start_element ($xw, 'task');
			xmlwriter_write_element ($xw, 'id', $trusted_id);
			xmlwriter_write_element ($xw, 'class', 'error');
			xmlwriter_write_element ($xw, 'text', $tasklist_class -> returnTask ($trusted_id));
			xmlwriter_end_element ($xw); // task
		}
		// Generate the XML file and echo it back to the client
		xmlwriter_end_element ($xw); // tasklist
		xmlwriter_end_document ($xw);
		header ('Content-type: text/xml');
		echo xmlwriter_output_memory ($xw);
	} else {
		// We don't know what action (add / cancel / delete / modify) we're being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
}

exit ();

?>