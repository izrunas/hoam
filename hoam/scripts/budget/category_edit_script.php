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
		$_HOAM_assessment_list = &$_HOAM_language ['budget']['assessment'];

		$budget_class = new HOAM_budget ('budget');
		$budget_category_class = new HOAM_budget_category ('budget_category');
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION ['category_information'])) {
			unset ($_SESSION ['category_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION ['error_class_item'])) {
			unset ($_SESSION ['error_class_item']);
		}
		
		if (isset ($_POST ['cancel'])) {
			// Just send them back to the admin page
			header ('Location: /admin/financial/category/');
		} elseif (isset ($_POST ['delete'])) {
			// Make sure the current user is the owner of the item, OR has admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION ['current_user']['id'])) {
				// Make sure we found the category
				$category_id = (string) $budget_category_class -> superClean ($_POST ['category|id'], $budget_category_class -> returnColumnSize ('id'));
				if ($budget_category_class -> idExists ($category_id)) {
					// Need to add a check that we aren't deleting a category that is in use by any budget entries.
					if ($budget_class -> returnCountCategoryId ($category_id)) {
						// Error Removing Item
						// This needs to be moved to a more specific error, as in 'The item is still in use, can't be deleted'.
						header ('Location: /error/inuse/');
					} else {
						if ($budget_category_class -> deleteId ($category_id)) {
							header ('Location: /admin/financial/category/');
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
			$_SESSION ['category_information'] = array ();
			$_SESSION ['error_class_item'] = new HOAM_errors ('errors');
			// Start populating the article_information array, and check for errors.
			if (isset ($_POST ['category|id'])) {
				$_SESSION ['category_information']['trusted_category|id'] = (string) $budget_category_class -> superClean ($_POST ['category|id'], $budget_category_class -> returnColumnSize ('id'));
				if ($budget_category_class -> idExists ($_SESSION ['category_information']['trusted_category|id'])) {
				} else {
					// The category is unknown.
					$_SESSION ['error_class_item'] -> addError ('category|id', $_HOAM_language ['errors']['budget']['category']['unknown-id']);
				}
			}
			$_SESSION ['category_information']['trusted_budget|category|flags'] = 0;
			if (isset ($_POST ['budget|category|type'])) {
				$_SESSION ['category_information']['trusted_budget|category|type'] = (int) $budget_category_class -> superClean ($_POST ['budget|category|type'], $budget_category_class -> returnColumnSize ('flags'));
				foreach ($_HOAM_assessment_list as &$key) {
					if ((int) $_HOAM_setting ['budget']['flags'][$key ['value']] == (int) $_SESSION ['category_information']['trusted_budget|category|type']) {
						break;
					}
				}
				if ($_HOAM_setting ['budget']['flags'][$key ['value']] == $_SESSION ['category_information']['trusted_budget|category|type']) {
					$_SESSION ['category_information']['trusted_budget|category|flags'] += $_SESSION ['category_information']['trusted_budget|category|type'];
				} else {
					// The budget type specified is unknown.
					$_SESSION ['error_class_item'] -> addError ('budget|category|type', $_HOAM_language ['errors']['budget']['category']['unknown-type']);
				}
			}
			if (isset ($_POST ['budget|category|parent'])) {
				$_SESSION ['category_information']['trusted_budget|category|parent'] = (string) $budget_category_class -> superClean ($_POST ['budget|category|parent'], $budget_category_class -> returnColumnSize ('parent_category'));
				if ($_SESSION ['category_information']['trusted_budget|category|parent'] == '0') {
					// Since we could be creating a new parent category, don't perform additional checks if the parent is blank.
					$_SESSION ['category_information']['trusted_budget|category|parent'] = NULL;
				} else {
					if ($budget_category_class -> idExists ($_SESSION ['category_information']['trusted_budget|category|parent'])) {
					} else {
						// The parent category is unknown.
						$_SESSION ['error_class_item'] -> addError ('budget|category|parent', $_HOAM_language ['errors']['budget']['category']['unknown-parent']);
					}
				}
			}
			if (isset ($_POST ['budget|category|name'])) {
				$_SESSION ['category_information']['trusted_budget|category|name'] = (string) ucfirst ($budget_category_class -> superClean ($_POST ['budget|category|name'], $budget_category_class -> returnColumnSize ('name')));
				// Make sure the name isn't too short.
				if (mb_strlen ($_SESSION ['category_information']['trusted_budget|category|name']) >= $_HOAM_setting ['budget']['minimum_name_length']) {
				} else {
					// The category name is too short.
					$_SESSION ['error_class_item'] -> addError ('budget|category|name', $_HOAM_language ['errors']['budget']['category']['name-too-short'], $_HOAM_setting ['budget']['minimum_name_length']);
				}
				// Need to add a check that the category specified doesn't already exist.
				if ($_SESSION ['category_information']['trusted_budget|category|parent'] == NULL) {
					$category_list = $budget_category_class -> listParentCategories ();
				} else {
					$category_list = $budget_category_class -> listChildCategories ($_SESSION ['category_information']['trusted_budget|category|parent']);
				}
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						if (strtoupper ($_SESSION ['category_information']['trusted_budget|category|name']) == strtoupper ($budget_category_class -> returnName ($category))) {
							// Make sure we're not reporting on our own id.
							if ($_SESSION ['category_information']['trusted_category|id'] != $category) {
								$_SESSION ['error_class_item'] -> addError ('budget|category|name', $_HOAM_language ['errors']['budget']['category']['duplicate']);
							}
						}
					}
				}
			}
			if (isset ($_POST ['budget|category|description'])) {
				$_SESSION ['category_information']['trusted_budget|category|description'] = (string) $budget_category_class -> superClean ($_POST ['budget|category|description'], $budget_category_class -> returnColumnSize ('description'));
				// Make sure the category description isn't too short.
				if (mb_strlen ($_SESSION ['category_information']['trusted_budget|category|description']) >= $_HOAM_setting ['budget']['minimum_description_length']) {
				} else {
					// The description is too short.
					$_SESSION ['error_class_item'] -> addError ('budget|category|description', sprintf ($_HOAM_language ['errors']['budget']['category']['description-too-short'], $_HOAM_setting ['budget']['minimum_description_length']));
				}
			}
			
			$_SESSION ['category_information']['trusted_budget|category|flags'] = 0;
			
			if (isset ($_POST ['budget|category|flag|interest'])) {
				$_SESSION ['category_information']['trusted_budget|category|flags'] += $_HOAM_setting ['budget']['flags']['apply_interest'];
			}
			if (isset ($_POST ['budget|category|flag|late'])) {
				$_SESSION ['category_information']['trusted_budget|category|flags'] += $_HOAM_setting ['budget']['flags']['apply_fee_late'];
			}
			if (isset ($_POST ['budget|category|flag|sales-tax'])) {
				$_SESSION ['category_information']['trusted_budget|category|flags'] += $_HOAM_setting ['budget']['flags']['apply_sales_tax'];
			}
			if (isset ($_POST ['budget|category|track'])) {
				if ((int) $_POST ['budget|category|track'] == 1) {
					$_SESSION ['category_information']['trusted_budget|category|flags'] += $_HOAM_setting ['budget']['flags']['category_income'];
				}
			}
			
			// Add the fee type to the flags
			$_SESSION ['category_information']['trusted_budget|category|flags'] += $_SESSION ['category_information']['trusted_budget|category|type'];
			
// print_r ($_POST); print_r ($_SESSION['category_information']); $_SESSION['error_class_item'] -> showErrors (); echo $_SESSION ['error_class_item'] -> errorCount; exit();
			
			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION ['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST ['add'])) {
					header ('location: /admin/financial/category/add/');
				} else {
					header ('location: /admin/financial/category/edit/' . $_SESSION ['category_information']['trusted_category|id']);
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST ['add'])) {
					$category_id = $budget_category_class -> add ($_SESSION ['current_user']['id']);
					if ($category_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					}
				} else {
					// Use the information gathered above.
					$category_id = $_SESSION ['category_information']['trusted_category|id'];
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$budget_category_class -> changeName ($category_id, $_SESSION ['category_information']['trusted_budget|category|name']);
				$budget_category_class -> changeParentCategory ($category_id, $_SESSION ['category_information']['trusted_budget|category|parent']);
				$budget_category_class -> changeDescription ($category_id, $_SESSION ['category_information']['trusted_budget|category|description']);
				$budget_category_class -> changeFlags ($category_id, $_SESSION ['category_information']['trusted_budget|category|flags']);
				$budget_category_class -> changeDateModified ($category_id);
				
				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION ['category_information'])) {
					unset ($_SESSION ['category_information']);
				}
				if (isset ($_SESSION ['error_class_item'])) {
					// Clear the error_class_item variable since everything worked ok.
					unset ($_SESSION ['error_class_item']);
				}
				
				header ('Location: /admin/financial/category/list/');
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
