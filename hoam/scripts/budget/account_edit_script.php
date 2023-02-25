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

// Is the current user allowed to add / edit violation categories?
if (isset ($_SESSION ['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
	($_HOAM_setting ['users']['ids']['root'] === $_SESSION ['current_user']['id']))) {
	if (isset ($_POST ['add']) OR isset ($_POST ['cancel']) OR isset ($_POST ['delete']) OR isset ($_POST ['modify'])) {

		$budget_class = new HOAM_budget ('budget');
		$budget_account_class = new HOAM_budget_account ('budget_account');
		
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION ['account_information'])) {
			unset ($_SESSION ['account_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION ['error_class_item'])) {
			unset ($_SESSION ['error_class_item']);
		}
		
		if (isset ($_POST ['cancel'])) {
			// Just send them back to the admin page
			header ('Location: /admin/financial/account/');
		} elseif (isset ($_POST ['delete'])) {
			// Make sure the current user is the owner of the item, OR has admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR 
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION ['current_user']['id'])) {
				// Make sure we found the account
				$account_id = (string) $budget_account_class -> superClean ($_POST ['account|id'], $budget_account_class -> returnColumnSize ('id'));
				if ($budget_account_class -> idExists ($account_id)) {
					// Need to add a check that we aren't deleting a category that is in use by any budget entries.
					if ($budget_class -> returnCountCategoryId ($account_id)) {
						// Error Removing Item
						// This needs to be moved to a more specific error, as in 'The item is still in use, can't be deleted'.
						header ('Location: /error/inuse/');
					} else {
						if ($budget_account_class -> deleteId ($account_id)) {
							header ('Location: /admin/financial/account/list/');
						} else {
							// Error Removing Item
							header ('Location: /error/remove/');
						}
					}
				} else {
					// The article couldn't be found
					header ('Location: /error/unknown_id/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or modifying an existing category.
			$_SESSION ['account_information'] = array ();
			$_SESSION ['error_class_item'] = new HOAM_errors ('errors');
			
			// Start populating the account_information array, and check for errors.
			if (isset ($_POST ['account|id'])) {
				$_SESSION ['account_information']['trusted_account|id'] = (string) $budget_account_class -> superClean ($_POST ['account|id'], $budget_account_class -> returnColumnSize ('id'));
				if ($budget_account_class -> idExists ($_SESSION ['account_information']['trusted_account|id'])) {
				} else {
					// The category is unknown.
					$_SESSION ['error_class_item'] -> addError ('account|id', $_HOAM_language ['errors']['budget']['account']['unknown-id']);
				}
			}
			if (isset ($_POST ['budget|account|name'])) {
				$_SESSION ['account_information']['trusted_budget|account|name'] = (string) $budget_account_class -> superClean ($_POST ['budget|account|name'], $budget_account_class -> returnColumnSize ('name'));
				// Make sure the name isn't too short.
				if (mb_strlen ($_SESSION ['account_information']['trusted_budget|account|name']) >= $_HOAM_setting ['budget']['minimum_name_length']) {
				} else {
					// The account name is too short.
					$_SESSION ['error_class_item'] -> addError ('budget|account|name', $_HOAM_language ['errors']['budget']['account']['name-too-short'], $_HOAM_setting ['budget']['minimum_name_length']);
				}
				// Need to add a check that the account specified doesn't already exist.
			}
			if (isset ($_POST ['budget|account|description'])) {
				$_SESSION ['account_information']['trusted_budget|account|description'] = (string) $budget_account_class -> superClean ($_POST ['budget|account|description'], $budget_account_class -> returnColumnSize ('description'));
				// Make sure the description isn't too short.
				if (mb_strlen ($_SESSION ['account_information']['trusted_budget|account|description']) >= $_HOAM_setting ['budget']['minimum_description_length']) {
				} else {
					// The account description is too short.
					$_SESSION ['error_class_item'] -> addError ('budget|account|description', $_HOAM_language ['errors']['budget']['account']['description-too-short'], $_HOAM_setting ['budget']['minimum_description_length']);
				}
				// Need to add a check that the account specified doesn't already exist.
			}
			if (isset ($_POST ['budget|account|number'])) {
				$_SESSION ['account_information']['trusted_budget|account|number'] = (string) $budget_account_class -> superClean ($_POST ['budget|account|number'], $budget_account_class -> returnColumnSize ('account_num'));
				// Make sure the number isn't too short.
				if (mb_strlen ($_SESSION ['account_information']['trusted_budget|account|number']) >= 0) {
				} else {
					// The account number is too short.
					$_SESSION ['error_class_item'] -> addError ('budget|account|number', $_HOAM_language ['errors']['budget']['account']['too_short']);
				}
				// Need to add a check that the account number specified doesn't already exist.
			}
			$_SESSION ['account_information']['trusted_budget|account|flags'] = 0;
									
// print_r ($_POST); print_r ($_SESSION['account_information']); $_SESSION['error_class_item'] -> showErrors (); exit();
			
			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION ['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST ['add'])) {
					header ('location: /admin/financial/account/add/');
				} else {
					header ('location: /admin/financial/account/edit/ ' . $_SESSION ['account_information']['trusted_account|id']);
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST ['add'])) {
					$account_id = $budget_account_class -> add ($_SESSION ['current_user']['id']);
					if ($account_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					}
				} else {
					// Use the information gathered above.
					$account_id = $_SESSION ['account_information']['trusted_account|id'];
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$budget_account_class -> changeAccountNum ($account_id, $_SESSION ['account_information']['trusted_budget|account|number']);
				$budget_account_class -> changeDescription ($account_id, $_SESSION ['account_information']['trusted_budget|account|description']);
				$budget_account_class -> changeFlags ($account_id, $_SESSION ['account_information']['trusted_budget|account|flags']);
				$budget_account_class -> changeName ($account_id, $_SESSION ['account_information']['trusted_budget|account|name']);
				$budget_account_class -> changeDateModified ($account_id);
				
				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION ['account_information'])) {
					unset ($_SESSION ['account_information']);
				}
				if (isset ($_SESSION ['error_class_item'])) {
					unset ($_SESSION ['error_class_item']);
				}
				
				header ('Location: /admin/financial/account/list/');
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
