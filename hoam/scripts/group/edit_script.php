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

// Is the current user allowed to add / edit groups?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		$group_class = new HOAM_group ('group_list');
		
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['group_information'])) {
			unset ($_SESSION['group_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			// Just send them back to the admin page
				header ('Location: /website/group/');
		} elseif (isset ($_POST['delete'])) {
			// Make sure the current user is the owner of the item, OR has admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
				// Make sure we found the group
				$trusted_group_id = (string) $group_class -> superClean ($_POST['group|id'], $group_class -> returnColumnSize ('id'));
				if ($group_class -> idExists ($trusted_group_id)) {
					// Need to add a check that we aren't deleting a group that is in use.
					if ($group_members_class -> returnCountMembers ($trusted_group_id)) {
						// Error Removing Item
						// This needs to be moved to a more specific erorr, as in 'The item is still in use, can't be deleted'.
						header ('Location: /error/inuse/');
					} else {
						if ($group_class -> deleteId ($trusted_group_id)) {
							header ('Location: /website/group/');
						} else {
							// Error Removing Item
							header ('Location: /error/remove/');
						}
					}
				} else {
					// The group couldn't be found
					header ('Location: /error/unknown_id/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or modifying an existing group.
			// Start populating the group_information array, and check for errors.
			$_SESSION['group_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['group|id'])) {
				$_SESSION['group_information']['trusted_group|id'] = (string) $group_class -> superClean ($_POST['group|id'], $group_class -> returnColumnSize ('id'));
				if ($group_class -> idExists ($_SESSION['group_information']['trusted_group|id'])) {
				} else {
					// The group is unknown.
					$_SESSION ['error_class_item'] -> addError ('group|id', $_HOAM_language ['errors']['generic']['invalid_id']);
				}
			}
			if (isset ($_POST['group|name'])) {
				$_SESSION['group_information']['trusted_group|name'] = $group_class -> superClean ($_POST['group|name'], $group_class -> returnColumnSize ('name'));
				// Make sure the name isn't too short.
				if (mb_strlen ($_SESSION['group_information']['trusted_group|name']) >= $_HOAM_setting ['group']['minimum_name_length']) {
				} else {
					// The name is too short.
					$_SESSION ['error_class_item'] -> addError ('group|name', sprintf ($_HOAM_language ['errors']['group']['name-too-short'], $_HOAM_setting ['group']['minimum_name_length']));
				}
				// Need to add a check that the name specified doesn't already exist.
			}
			if (isset ($_POST['group|description'])) {
				$_SESSION['group_information']['trusted_group|description'] = $group_class -> superClean ($_POST['group|description'], $group_class -> returnColumnSize ('description'));
				// Make sure the description isn't too short.
				if (mb_strlen ($_SESSION['group_information']['trusted_group|description']) >= $_HOAM_setting ['group']['minimum_description_length']) {
				} else {
					// The description is too short.
					$_SESSION ['error_class_item'] -> addError ('group|description', sprintf ($_HOAM_language ['errors']['group']['description-too-short'], $_HOAM_setting ['group']['minimum_description_length']));
				}
			}

// print_r ($_POST); print_r ($_SESSION["group_information"]); $_SESSION['error_class_item'] -> showErrors (); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /website/group/add/');
				} else {
					header ('location: /website/group/edit/' . $_SESSION['group_information']['trusted_group|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST['add'])) {
					$group_id = $group_class -> add ($_SESSION['current_user']['id']);
					if ($group_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						$group_class -> changeDateModified ($group_id);
					}
				} else {
					// Use the information gathered above.
					$group_id = $_SESSION['group_information']['trusted_group|id'];
					$group_class -> changeDateModified ($group_id);
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$group_class -> changeName ($group_id, $_SESSION['group_information']['trusted_group|name']);
				$group_class -> changeDescription ($group_id, $_SESSION['group_information']['trusted_group|description']);

				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['group_information'])) {
					unset ($_SESSION['group_information']);
				}
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}

				header ('Location: /website/group/');
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

?>
