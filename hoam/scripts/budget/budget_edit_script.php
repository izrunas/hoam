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
if (isset ($_SESSION ['current_user']['id']) and
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION ['current_user']['id']) OR
	($_HOAM_setting ['users']['ids']['root'] === $_SESSION ['current_user'] ['id']))) {
	if (isset ($_POST ['add']) OR isset ($_POST ['cancel']) OR isset ($_POST ['delete']) OR isset ($_POST ['modify'])) {
		$budget_budget_class = new HOAM_budget_budget ('budget_budget');
		$budget_category_class = new HOAM_budget_category ('budget_category');
		$budget_tracking_class = new HOAM_budget_track ('budget_track');
		
		// If this is the second (or greater) time through the script (ie, there
		// were errors that had to be fixed before committing the changes) get
		// rid of the old cached information the user entered, we're going to
		// populate it with information just received from the form posting.
		if (isset ($_SESSION ['budget_information'])) {
			unset ($_SESSION ['budget_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION ['error_class_item'])) {
			unset ($_SESSION ['error_class_item']);
		}
		
		if (isset ($_POST ['cancel'])) {
			// Just send them back to the admin page
			header ('Location: /admin/financial/budget/');
		} elseif (isset ($_POST ['delete'])) {
			// Make sure the current user is the owner of the item, OR has admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR 
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION ['current_user']['id'])) {
				// Make sure we found the account
				$budget_id = (string) $budget_budget_class -> superClean ($_POST ['budget|budget|id'], $budget_budget_class -> returnColumnSize ('id'));
				if ($budget_budget_class -> idExists ($budget_id)) {
					$tracking_list = $budget_tracking_class -> listCategoryId ($budget_id);
					foreach ($tracking_list as $track) {
						$budget_tracking_class -> deleteId ($track);
					}
					if ($budget_budget_class -> deleteId ($budget_id)) {
						header ('Location: /admin/financial/budget/');
					} else {
						// Error Removing Item
						header ('Location: /error/remove/');
					}
				} else {
					// The id couldn't be found
					header ('Location: /error/unknown_id/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or modifying an existing budget.
			$_SESSION ['budget_information'] = array ();
			$_SESSION ['error_class_item'] = new HOAM_errors ('errors');
			
			// Start populating the budget_information array, and check for errors.
			if (isset ($_POST ['budget|budget|id'])) {
				$_SESSION ['budget_information']['trusted_budget|budget|id'] = (string) $budget_budget_class -> superClean ($_POST ['budget|budget|id'], $budget_budget_class -> returnColumnSize ('id'));
				if ($budget_budget_class -> idExists ($_SESSION ['budget_information']['trusted_budget|budget|id'])) {
				} else {
					$_SESSION ['error_class_item'] -> addError ('budget|budget|id', $_HOAM_language ['errors']['budget']['account']['unknown-id']);
				}
			}

			$_SESSION ['budget_information']['trusted_budget|budget|name'] = (string) $budget_budget_class -> superClean ($_POST ['budget|budget|name'], $budget_budget_class -> returnColumnSize ('name'));
			// Make sure the name isn't too short.
			if (mb_strlen ($_SESSION ['budget_information']['trusted_budget|budget|name']) >= $_HOAM_setting ['budget']['minimum_name_length']) {
			} else {
				// The account name is too short.
				$_SESSION ['error_class_item'] -> addError ('budget|budget|name', $_HOAM_language ['errors']['budget']['name-too-short'], $_HOAM_setting ['budget']['minimum_name_length']);
			}

			$_SESSION['budget_information']['trusted_budget|budget|end'] = (string) $budget_budget_class -> superClean ($_POST['budget|budget|end'], $budget_budget_class -> returnColumnSize ('dateend'));
			// Make sure we have a valid date
			if (checkdate (date ('m', strtotime ($_SESSION['budget_information']['trusted_budget|budget|end'])), date ('d', strtotime ($_SESSION['budget_information']['trusted_budget|budget|end'])), date ('Y', strtotime ($_SESSION['budget_information']['trusted_budget|budget|end'])))) {
			} else {
				$_SESSION['error_class_item'] -> addError ('budget|budget|end', $_HOAM_language ['errors']['generic']['invalid-date']);
			}

			$_SESSION['budget_information']['trusted_budget|budget|start'] = (string) $budget_budget_class -> superClean ($_POST['budget|budget|start'], $budget_budget_class -> returnColumnSize ('datestart'));
			// Make sure we have a valid date
			if (checkdate (date ('m', strtotime ($_SESSION['budget_information']['trusted_budget|budget|start'])), date ('d', strtotime ($_SESSION['budget_information']['trusted_budget|budget|start'])), date ('Y', strtotime ($_SESSION['budget_information']['trusted_budget|budget|start'])))) {
			} else {
				$_SESSION['error_class_item'] -> addError ('budget|budget|start', $_HOAM_language ['errors']['generic']['invalid-date']);
			}

			$_SESSION['budget_information']['trusted_budget|budget|annual'] = (int) $_POST['budget|budget|annual'];
			$_SESSION['budget_information']['trusted_budget|budget|public'] = (int) $_POST['budget|budget|public'];

			$_SESSION['budget_information']['trusted_budget|budget|categories'] = array ();
			// We need to make sure we preserve any category information
			// already entered for this budget in case we only modified
			// the name, dates, etc.
			foreach ($_POST['budget|budget|categories'] as $category) {
				$trusted_category = $budget_category_class -> superClean ($category, $budget_category_class -> returnColumnSize ('id'));
				// Verify the category id is valid.
				if ($budget_category_class -> idExists ($trusted_category)) {
					array_push ($_SESSION['budget_information']['trusted_budget|budget|categories'], $trusted_category);
				} else {
					$_SESSION['error_class_item'] -> addError ('budget|budget|budget|categories', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			
// print_r ($_POST); print_r ($_SESSION['budget_information']); $_SESSION['error_class_item'] -> showErrors (); exit();
			
			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION ['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST ['add'] )) {
					header ('location: /admin/financial/budget/add/');
				} else {
					header ('location: /admin/financial/budget/edit/' . $_SESSION ['budget_information']['trusted_budget|budget|id']);
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST ['add'])) {
					$budget_id = $budget_budget_class -> add ($_SESSION ['current_user']['id']);
					if ($budget_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					}
				} else {
					// Use the information gathered above.
					$budget_id = $_SESSION ['budget_information']['trusted_budget|budget|id'];
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$budget_budget_class -> changeName ($budget_id, $_SESSION ['budget_information']['trusted_budget|budget|name']);
				$budget_budget_class -> changeDateEnd ($budget_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['budget_information']['trusted_budget|budget|end'])));
				$budget_budget_class -> changeDateStart ($budget_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['budget_information']['trusted_budget|budget|start'])));
				$budget_budget_class -> changeDateModified ($budget_id);
				if (is_true ($_SESSION['budget_information']['trusted_budget|budget|annual'])) {
					$budget_budget_class -> setBit ($budget_id, $_HOAM_setting ['budget']['flags']['annual']);
				} else {
					$budget_budget_class -> clearBit ($budget_id, $_HOAM_setting ['budget']['flags']['annual']);
				}
				if (is_true ($_SESSION['budget_information']['trusted_budget|budget|public'])) {
					$budget_budget_class -> setBit ($budget_id, $_HOAM_setting ['budget']['flags']['public']);
				} else {
					$budget_budget_class -> clearBit ($budget_id, $_HOAM_setting ['budget']['flags']['public']);
				}
				
				// Before we update the categories being tracked, see if there
				// are any categories already being tracked (eg, we're editing
				// an existing budget), and copy the budget amounts listed.
				$existing_tracking_list = $budget_tracking_class -> listCategoryId ($budget_id);
				if (is_array ($existing_tracking_list)) {
					$budget_amounts = array ();
					foreach ($existing_tracking_list as $track) {
						if (in_array ($budget_tracking_class -> returnCategoryId ($track), $_SESSION['budget_information']['trusted_budget|budget|categories'])) {
							array_push ($budget_amounts, (array ($budget_tracking_class -> returnCategoryId ($track), $budget_tracking_class -> returnAmount ($track))));
						}
					}
					// Now, go through and delete each of the old categories.
					foreach ($existing_tracking_list as $track) {
						$budget_tracking_class -> deleteId ($track);
					}
				}
				foreach ($_SESSION['budget_information']['trusted_budget|budget|categories'] as $category) {
					// We want to make sure we preserve any category information
					// already entered for this budget in case we only modified
					// the name, dates, etc.
					$tracking_id = $budget_tracking_class -> add ($_SESSION ['current_user']['id']);
					$budget_tracking_class -> changeBudgetId ($tracking_id, $budget_id);
					$budget_tracking_class -> changeCategoryId ($tracking_id, $category);
					$budget_tracking_class -> changeAmount ($tracking_id, 0);
				}
				// Restore the amounts for tracked categories saved above
				if (is_array ($budget_amounts)) {
					foreach ($budget_amounts as $category) {
						$id = $budget_tracking_class -> returnIdForCategory ($category[0], $budget_id);
						$budget_tracking_class -> changeAmount ($id, $category[1]);
					}
				}
				
				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION ['budget_information'])) {
					unset ($_SESSION ['budget_information']);
				}
				if (isset ($_SESSION ['error_class_item'])) {
					unset ($_SESSION ['error_class_item']);
				}
				
				header ('Location: /admin/financial/budget/entry/');
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
