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
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['cancel']) OR isset ($_POST['modify'])) {
		$group_class = new HOAM_group ('group_list');
		$user_class = new HOAM_user ('users');
		
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
		} else {
			// If we're still here, then we must be modifying an existing group.
			// Start populating the group_information array, and check for errors.
			$_SESSION['group_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			$_SESSION['group_information']['trusted_group|id'] = (string) $group_class -> superClean ($_POST['group|id'], $group_class -> returnColumnSize ('id'));
			if ($group_class -> idExists ($_SESSION['group_information']['trusted_group|id'])) {
			} else {
				// The group is unknown.
				$_SESSION ['error_class_item'] -> addError ('group|id', $_HOAM_language ['errors']['generic']['invalid_id']);
			}
			$_SESSION['group_information']['trusted_group|groups'] = array ();
			foreach ($_POST['group|groups'] as $group) {
				$trusted_group = $group_class -> superClean ($group, $group_class -> returnColumnSize ('id'));
				if ($group_class -> idExists ($trusted_group)) {
					array_push ($_SESSION['group_information']['trusted_group|groups'], $trusted_group);
				} else {
					$_SESSION ['error_class_item'] -> addError ('group|groups[]', $_HOAM_language ['errors']['generic']['invalid-id']);						
				}
			}
			$_SESSION['group_information']['trusted_group|users'] = array ();
			foreach ($_POST['group|users'] as $user) {
				$trusted_user = $user_class -> superClean ($user, $user_class -> returnColumnSize ('id'));
				if ($user_class -> idExists ($trusted_user)) {
					array_push ($_SESSION['group_information']['trusted_group|users'], $trusted_user);
				} else {
					$_SESSION ['error_class_item'] -> addError ('group|users[]', $_HOAM_language ['errors']['generic']['invalid-id']);						
				}
			}
			
// print_r ($_POST); print_r ($_SESSION["group_information"]); $_SESSION['error_class_item'] -> showErrors (); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
					header ('location: /website/group/membership/' . $_SESSION['group_information']['trusted_group|id'] . '/');
			} else {
				$group_id = $_SESSION['group_information']['trusted_group|id'];
				// We need to remove all of the existing group memberships and
				// start over with the information provided.
				$existing_membership_list = $group_members_class -> listByGroup ($group_id);
				foreach ($existing_membership_list as $member) {
					$group_members_class -> deleteId ($member);
				}
				// Now, let's add our new members
				foreach ($_SESSION['group_information']['trusted_group|groups'] as $group) {
					$id = $group_members_class -> add ($_SESSION['current_user']['id']);
					$group_members_class -> changeGroupId ($id, $group_id);
					$group_members_class -> changeMemberId ($id, $group);
					$group_members_class -> changeFlags ($id, $_HOAM_setting ['group']['flags']['group']);
				}
				foreach ($_SESSION['group_information']['trusted_group|users'] as $user) {
					$id = $group_members_class -> add ($_SESSION['current_user']['id']);
					$group_members_class -> changeGroupId ($id, $group_id);
					$group_members_class -> changeMemberId ($id, $user);
					$group_members_class -> changeFlags ($id, $_HOAM_setting ['group']['flags']['user']);
				}
				
				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['group_information'])) {
					unset ($_SESSION['group_information']);
				}
				if (isset ($_SESSION['error_class_item'])) {
					// Clear the error_class_item variable since everything worked ok.
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
